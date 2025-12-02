<?php

namespace App\Events;

use Bitrix\Main\Loader;
use Bitrix\Crm\DealTable;
use CIBlockElement;

class CrmFieldsHandler
{
    public static function onDealAfterUpdate(&$arFields)
    {
        if (!Loader::includeModule('iblock') || !Loader::includeModule('crm'))  return;

        // IBLOCK = 20 
        if (!empty($arFields['UF_IB_SYNC'])) {
            DealTable::Update($arFields['ID'], ['UF_IB_SYNC' => false]);
            return;
        }

        $elementId = self::getZayyavkaByDealId($arFields['ID']);
        if (!$elementId) return;

        \App\Debug\Mylog::addLog($arFields, 'Crm-arFields', '', __FILE__, __LINE__);

        CIBlockElement::SetPropertyValuesEx(
            $elementId,
            20,
            [
                'AMOUNT' => $arFields['OPPORTUNITY'],
                'ASSIGNED' => $arFields['ASSIGNED_BY_ID'],
                'UF_CRM_SYNC' => true
            ]
        );
        // IBLOCK = 20 
    }

    public static function onDealBeforeDelete($id, &$arFields)
    {
        if (!Loader::includeModule('iblock'))  return true;

        $elementId = self::getZayyavkaByDealId($id);

        if ($elementId) {
            global $APPLICATION;
            $APPLICATION->throwException(
                'Невозможно удалить сделку, так как она привязана к <a href="/services/lists/20/element/0/' . $elementId . '/?list_section_id=">заявке</a>'
            );
            return false;
        }

        return true;
    }

    private static function getZayyavkaByDealId($dealId)
    {
        $res = CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => 20,
                'PROPERTY_DEAL' => $dealId
            ],
            false,
            false,
            ['ID', 'NAME']
        );
        $element = $res->Fetch();
        \App\Debug\Mylog::addLog($element, 'Crm-getZayyavkaByDealId', '', __FILE__, __LINE__);
        return ($element) ? $element : false;
    }
}
