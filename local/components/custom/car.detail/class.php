<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class CarDetailComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        CModule::IncludeModule('crm');
        CModule::IncludeModule('iblock');
        
        $this->arResult = $this->getCarData();
        $this->includeComponentTemplate();
    }
    
    private function getCarData()
    {
        $carId = (int)$_REQUEST['car_id'];
        if (!$carId) {
            return ['ERROR' => 'Автомобиль не найден', 'HAS_ERROR' => true];
        }
        
        try {
            // 1. Получаем данные об автомобиле
            $factory = \Bitrix\Crm\Service\Container::getInstance()->getFactory(1054);
            
            if (!$factory) {
                return ['ERROR' => 'Смарт-процесс не найден', 'HAS_ERROR' => true];
            }
            
            $carItem = $factory->getItem($carId);
            if (!$carItem) {
                return ['ERROR' => 'Автомобиль не найден', 'HAS_ERROR' => true];
            }
            
            $carData = [
                'ID' => $carItem->getId(),
                'BRAND' => $this->getLinkedEntityName($carItem->get('UF_CRM_6_BRAND'), 1040),
                'MODEL' => $this->getLinkedEntityName($carItem->get('UF_CRM_6_MODEL'), 1046),
                'VIN' => $carItem->get('UF_CRM_6_VIN') ?? '',
                'NUMBER' => $carItem->get('UF_CRM_6_NUMBER') ?? '—',
                'YEAR' => $carItem->get('UF_CRM_6_YEAR') ?? '',
                'MILEAGE' => $carItem->get('UF_CRM_6_MILEAGE') ?? '',
                'COLOR' => $carItem->get('UF_CRM_6_COLOR') ?? '',
                'OWNER_NAME' => $this->getContactName($carItem->get('UF_CRM_6_CONTACT'))
            ];
            
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
            
        } catch (Exception $e) {
            return ['ERROR' => 'Ошибка: ' . $e->getMessage(), 'HAS_ERROR' => true];
        }
    }
    
    private function getLinkedEntityName($entityId, $entityTypeId)
    {
        if (!$entityId) return '—';
        
        try {
            $factory = \Bitrix\Crm\Service\Container::getInstance()->getFactory($entityTypeId);
            if ($factory) {
                $item = $factory->getItem($entityId);
                if ($item) {
                    return $item->getTitle();
                }
            }
        } catch (Exception $e) {
            // Игнорируем ошибку
        }
        
        return '—';
    }
    
    private function getContactName($contactId)
    {
        if (!$contactId) return '—';
        
        try {
            $contact = CCrmContact::GetByID($contactId);
            if ($contact) {
                $name = trim($contact['NAME'] . ' ' . $contact['LAST_NAME']);
                return $name ?: '—';
            }
        } catch (Exception $e) {
            // Игнорируем ошибку
        }
        
        return '—';
    }
    
    private function getActiveDeals($carId)
    {
        $deals = [];
        
        try {
            // Получаем только НЕзавершенные сделки
            $finalStages = ['C1:WON', 'C1:LOSE', 'C1:APOLOGY'];
            
            // ИСПРАВЛЕНО: первый параметр - массив сортировки, а не false
            $dbDeals = CCrmDeal::GetListEx(
                [], // Пустой массив для сортировки
                [
                    '=UF_CRM_1770588718' => $carId, // Поле связи с авто
                    '=CATEGORY_ID' => 1, // Сервисные сделки
                    '!STAGE_ID' => $finalStages // Исключаем финальные
                ],
                false, // Группировка
                false, // Навигация
                ['ID', 'TITLE', 'DATE_CREATE', 'STAGE_ID', 'ASSIGNED_BY_ID', 'OPPORTUNITY']
            );
            
            if ($dbDeals) {
                while ($deal = $dbDeals->Fetch()) {
                    // Получаем имя ответственного
                    $user = CUser::GetByID($deal['ASSIGNED_BY_ID'])->Fetch();
                    $assignedByName = $user ? trim($user['NAME'] . ' ' . $user['LAST_NAME']) : '—';
                    
                    // Получаем товары из сделки
                    $productRows = [];
                    if (class_exists('\CCrmProductRow')) {
                        $productRows = \CCrmProductRow::LoadRows('D', $deal['ID']);
                    }
                    
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
            }
            
        } catch (Exception $e) {
            // Логируем ошибку, но продолжаем работу
            AddMessage2Log('Ошибка получения сделок: ' . $e->getMessage());
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