<?php

namespace App\Events;

use Bitrix\Main\Loader;

class CrmFieldsHandler
{
    public static function onDealAfterUpdate(&$arFields)
    {
        if (!CModule::IncludeModule('iblock') || !CModule::IncludeModule('crm')) return;

        // IBLOCK = 20 
        if (!empty($arFields['UF_IB_SYNC'])) {
            CCrmDeal::Update($arFields['ID'], ['UF_IB_SYNC' => false]);
            return;
        }

        $elementId = self::getZayyavkaByDealId($arFields['ID']);
        if (!$elementId) return;

        CIBlockElement::SetPropertyValuesEx(
            $elementId,
            false,
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
        if (!CModule::IncludeModule('iblock')) return true;

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
        return ($element = $res->Fetch()) ? $element : false;
    }
}
