<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class DealCarFilterComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        // Проверяем, что мы на странице сделки
        global $APPLICATION;
        $currentPage = $APPLICATION->GetCurPage();
        
        if (!preg_match('#/crm/deal/(edit|details)/#', $currentPage)) {
            return;
        }
        
        // Получаем ID текущей сделки из URL или REQUEST
        $dealId = $this->getDealId();
        
        if ($dealId) {
            // Получаем ID связанного контакта из сделки
            $this->arResult['CONTACT_ID'] = $this->getDealContactId($dealId);
        } else {
            $this->arResult['CONTACT_ID'] = null;
        }
        
        // ID смарт-процесса "Гараж" (замените на ваш)
        $this->arResult['GARAGE_ENTITY_ID'] = 1054;
        
        // Код поля автомобиля
        $this->arResult['CAR_FIELD_CODE'] = 'UF_CRM_1770716463';
        
        $this->includeComponentTemplate();
    }
    
    private function getDealId()
    {
        $dealId = null;
        
        // Пытаемся получить ID из URL /crm/deal/edit/123/
        if (preg_match('#/crm/deal/(edit|details|show)/(\d+)/#', $_SERVER['REQUEST_URI'], $matches)) {
            $dealId = $matches[2];
        }
        
        // Или из GET параметров
        if (!$dealId && isset($_GET['id'])) {
            $dealId = intval($_GET['id']);
        }
        
        return $dealId;
    }
    
    private function getDealContactId($dealId)
    {
        // Получаем контакт из сделки через API
        $contactId = null;
        
        if (CModule::IncludeModule('crm')) {
            $deal = CCrmDeal::GetByID($dealId, false);
            if ($deal && isset($deal['CONTACT_ID'])) {
                $contactId = $deal['CONTACT_ID'];
            }
        }
        
        return $contactId;
    }
}