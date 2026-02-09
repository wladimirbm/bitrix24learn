<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class CarDetailComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        $this->arResult = $this->getCarData();
        $this->includeComponentTemplate();
    }
    
    private function getCarData()
    {
        $carId = (int)$_REQUEST['car_id'];
        if (!$carId) {
            return ['ERROR' => 'Автомобиль не найден', 'HAS_ERROR' => true];
        }
        
        // 1. Получаем данные об автомобиле через старый API
        CModule::IncludeModule('crm');
        CModule::IncludeModule('iblock');
        
        // Для смарт-процессов используем CCrmDynamic
        $carData = [];
        
        // Способ 1: Через кастомную таблицу (если знаем имя таблицы)
        $dbCar = CCrmDynamic::GetList(
            [],
            [
                'ID' => $carId,
                'ENTITY_TYPE_ID' => 1054
            ],
            false,
            false,
            ['*', 'UF_*']
        );
        
        if ($car = $dbCar->Fetch()) {
            $carData = [
                'ID' => $car['ID'],
                'BRAND' => $this->getLinkedEntityName($car['UF_CRM_6_BRAND'], 1040),
                'MODEL' => $this->getLinkedEntityName($car['UF_CRM_6_MODEL'], 1046),
                'VIN' => $car['UF_CRM_6_VIN'] ?? '',
                'NUMBER' => $car['UF_CRM_6_NUMBER'] ?? '—',
                'YEAR' => $car['UF_CRM_6_YEAR'] ?? '',
                'MILEAGE' => $car['UF_CRM_6_MILEAGE'] ?? '',
                'COLOR' => $car['UF_CRM_6_COLOR'] ?? '',
                'OWNER_NAME' => $this->getContactName($car['UF_CRM_6_CONTACT'])
            ];
        } else {
            // Способ 2: Через прямое обращение к базе
            $carData = $this->getCarFromDB($carId);
        }
        
        if (empty($carData)) {
            return ['ERROR' => 'Автомобиль не найден в базе', 'HAS_ERROR' => true];
        }
        
        // 2. Получаем активные сделки
        $deals = $this->getActiveDeals($carId);
        $carData['ACTIVE_DEALS_COUNT'] = count($deals);
        
        // 3. Определяем статус авто
        if (count($deals) > 0) {
            $carData['STATUS_TEXT'] = 'В РАБОТЕ';
            $carData['STATUS_COLOR'] = '#e74c3c';
            $carData['STATUS_DESCRIPTION'] = 'В работе';
        } else {
            $carData['STATUS_TEXT'] = 'СВОБОДЕН';
            $carData['STATUS_COLOR'] = '#27ae60';
            $carData['STATUS_DESCRIPTION'] = 'Свободен';
        }
        
        return [
            'CAR' => $carData,
            'DEALS' => $deals,
            'HAS_ERROR' => false
        ];
    }
    
    private function getCarFromDB($carId)
    {
        global $DB;
        
        $query = "
            SELECT 
                c.*,
                uf.UF_CRM_6_BRAND,
                uf.UF_CRM_6_MODEL,
                uf.UF_CRM_6_VIN,
                uf.UF_CRM_6_NUMBER,
                uf.UF_CRM_6_YEAR,
                uf.UF_CRM_6_MILEAGE,
                uf.UF_CRM_6_COLOR,
                uf.UF_CRM_6_CONTACT
            FROM b_crm_dynamic_1054 c
            LEFT JOIN b_uts_crm_dynamic_1054 uf ON c.ID = uf.VALUE_ID
            WHERE c.ID = " . (int)$carId . "
        ";
        
        $result = $DB->Query($query);
        if ($car = $result->Fetch()) {
            return [
                'ID' => $car['ID'],
                'BRAND' => $this->getLinkedEntityName($car['UF_CRM_6_BRAND'], 1040),
                'MODEL' => $this->getLinkedEntityName($car['UF_CRM_6_MODEL'], 1046),
                'VIN' => $car['UF_CRM_6_VIN'] ?? '',
                'NUMBER' => $car['UF_CRM_6_NUMBER'] ?? '—',
                'YEAR' => $car['UF_CRM_6_YEAR'] ?? '',
                'MILEAGE' => $car['UF_CRM_6_MILEAGE'] ?? '',
                'COLOR' => $car['UF_CRM_6_COLOR'] ?? '',
                'OWNER_NAME' => $this->getContactName($car['UF_CRM_6_CONTACT'])
            ];
        }
        
        return [];
    }
    
    private function getLinkedEntityName($entityId, $entityTypeId)
    {
        if (!$entityId) return '—';
        
        // Получаем название связанной сущности
        $dbEntity = CCrmDynamic::GetList(
            [],
            ['ID' => $entityId, 'ENTITY_TYPE_ID' => $entityTypeId],
            false,
            false,
            ['TITLE']
        );
        
        if ($entity = $dbEntity->Fetch()) {
            return $entity['TITLE'];
        }
        
        return '—';
    }
    
    private function getContactName($contactId)
    {
        if (!$contactId) return '—';
        
        $contact = CCrmContact::GetByID($contactId);
        if (!$contact) return '—';
        
        $name = trim($contact['NAME'] . ' ' . $contact['LAST_NAME']);
        return $name ?: '—';
    }
    
    private function getActiveDeals($carId)
    {
        $deals = [];
        
        if (!CModule::IncludeModule('crm')) {
            return $deals;
        }
        
        // Получаем только НЕзавершенные сделки
        $finalStages = ['C1:WON', 'C1:LOSE', 'C1:APOLOGY'];
        
        $dbDeals = CCrmDeal::GetList(
            ['DATE_CREATE' => 'DESC'],
            [
                '=UF_CRM_1770588718' => $carId, // Поле связи с авто
                '=CATEGORY_ID' => 1, // Сервисные сделки
                '!STAGE_ID' => $finalStages // Исключаем финальные
            ],
            false,
            false,
            ['ID', 'TITLE', 'DATE_CREATE', 'STAGE_ID', 'ASSIGNED_BY_ID', 'OPPORTUNITY']
        );
        
        while ($deal = $dbDeals->Fetch()) {
            // Получаем имя ответственного
            $user = CUser::GetByID($deal['ASSIGNED_BY_ID'])->Fetch();
            $assignedByName = $user ? trim($user['NAME'] . ' ' . $user['LAST_NAME']) : '—';
            
            // Получаем товары из сделки
            $productRows = \CCrmProductRow::LoadRows('D', $deal['ID']);
            $productsFormatted = [];
            
            foreach ($productRows as $product) {
                $productsFormatted[] = [
                    'NAME' => $product['PRODUCT_NAME'],
                    'QUANTITY' => $product['QUANTITY']
                ];
            }
            
            $deals[] = [
                'ID' => $deal['ID'],
                'TITLE' => $deal['TITLE'] ?: 'Сделка #' . $deal['ID'],
                'DATE_CREATE' => $deal['DATE_CREATE'],
                'STAGE_ID' => $deal['STAGE_ID'],
                'STAGE_NAME' => $this->getStageName($deal['STAGE_ID']),
                'ASSIGNED_BY_ID' => $deal['ASSIGNED_BY_ID'],
                'ASSIGNED_BY_NAME' => $assignedByName,
                'OPPORTUNITY' => $deal['OPPORTUNITY'],
                'PRODUCT_ROWS' => $productsFormatted
            ];
        }
        
        return $deals;
    }
    
    private function getStageName($stageId)
    {
        $stageNames = [
            'C1:NEW' => 'Приемка',
            'C1:EXECUTING' => 'Ремонт',
            'C1:1' => 'Диагностика',
            'C1:2' => 'Ожидание запчастей',
            'C1:3' => 'Ремонт',
            'C1:4' => 'Проверка'
        ];
        
        return $stageNames[$stageId] ?? $stageId;
    }
}
?>