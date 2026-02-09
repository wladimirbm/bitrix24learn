<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

class CarDetailComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        $this->arResult = $this->getCarData();
        $this->includeComponentTemplate();
    }
    
    private function getCarData()
    {
        Loader::includeModule('crm');

        $carId = (int)$_REQUEST['car_id'];
        if (!$carId) {
            return ['ERROR' => GetMessage('ERROR_NO_CAR')];
        }
        
        // 1. Получаем данные об автомобиле
        $factory = \Bitrix\Crm\Service\Container::getInstance()->getFactory(1054);
        
        if (!$factory) {
            return ['ERROR' => GetMessage('ERROR_NO_ACCESS')];
        }
        
        $carItem = $factory->getItem($carId);
        if (!$carItem) {
            return ['ERROR' => GetMessage('ERROR_NO_CAR')];
        }
        
        $carData = [
            'ID' => $carItem->getId(),
            'BRAND' => $this->getBrandName($carItem->get('UF_CRM_6_BRAND')),
            'MODEL' => $this->getModelName($carItem->get('UF_CRM_6_MODEL')),
            'VIN' => $carItem->get('UF_CRM_6_VIN') ?? '',
            'NUMBER' => $carItem->get('UF_CRM_6_NUMBER') ?? GetMessage('EMPTY_VALUE'),
            'YEAR' => $carItem->get('UF_CRM_6_YEAR') ?? '',
            'MILEAGE' => $carItem->get('UF_CRM_6_MILEAGE') ?? '',
            'COLOR' => $carItem->get('UF_CRM_6_COLOR') ?? '',
            'OWNER_NAME' => $this->getOwnerName($carItem->get('UF_CRM_6_CONTACT'))
        ];
        
        // 2. Получаем активные сделки
        $deals = $this->getActiveDeals($carId);
        $carData['ACTIVE_DEALS_COUNT'] = count($deals);
        
        // 3. Определяем статус авто
        if (count($deals) > 0) {
            $carData['STATUS_TEXT'] = GetMessage('CAR_STATUS_IN_WORK');
            $carData['STATUS_COLOR'] = '#e74c3c';
            $carData['STATUS_DESCRIPTION'] = GetMessage('CAR_STATUS_IN_WORK_TEXT');
        } else {
            $carData['STATUS_TEXT'] = GetMessage('CAR_STATUS_FREE');
            $carData['STATUS_COLOR'] = '#27ae60';
            $carData['STATUS_DESCRIPTION'] = GetMessage('CAR_STATUS_FREE_TEXT');
        }
        
        return [
            'CAR' => $carData,
            'DEALS' => $deals,
            'HAS_ERROR' => false
        ];
    }
    
    private function getBrandName($brandId)
    {
        if (!$brandId) return GetMessage('EMPTY_VALUE');
        
        // Если это привязка к элементам CRM, получаем название
        $item = CCrmDeal::GetByID($brandId);
        return $item ? $item['TITLE'] : GetMessage('EMPTY_VALUE');
    }
    
    private function getModelName($modelId)
    {
        if (!$modelId) return GetMessage('EMPTY_VALUE');
        
        // Если это привязка к элементам CRM, получаем название
        $item = CCrmDeal::GetByID($modelId);
        return $item ? $item['TITLE'] : GetMessage('EMPTY_VALUE');
    }
    
    private function getOwnerName($contactId)
    {
        if (!$contactId) return GetMessage('EMPTY_VALUE');
        
        $contact = CCrmContact::GetByID($contactId);
        if (!$contact) return GetMessage('EMPTY_VALUE');
        
        $name = trim($contact['NAME'] . ' ' . $contact['LAST_NAME']);
        return $name ?: GetMessage('EMPTY_VALUE');
    }
    
    private function getActiveDeals($carId)
    {
        $deals = [];
        
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
            $assignedByName = $user ? trim($user['NAME'] . ' ' . $user['LAST_NAME']) : GetMessage('EMPTY_VALUE');
            
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
                'TITLE' => $deal['TITLE'] ?: GetMessage('DEAL_TITLE') . ' #' . $deal['ID'],
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