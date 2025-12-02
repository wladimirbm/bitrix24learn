<?php

namespace App\Events;

use Bitrix\Main\Loader;
use Bitrix\Crm\DealTable;
use CIBlockElement;
use CAdminException;

class CrmFieldsHandler
{
    public static function onDealAfterUpdate(&$arFields)
    {
        if (!Loader::includeModule('iblock') || !Loader::includeModule('crm'))  return;

        // IBLOCK = 20 
        if (!empty($arFields['UF_IB_SYNC'])) {
            DealTable::Update($arFields['ID'], ['UF_IB_SYNC' => 0]);
            return;
        }

        $elementId = self::getZayyavkaByDealId($arFields['ID'])['ID'];
        if (!$elementId) return;

        //\App\Debug\Mylog::addLog($arFields, 'Crm-arFields', '', __FILE__, __LINE__);

        $res = CIBlockElement::SetPropertyValuesEx(
            $elementId,
            20,
            [
                'AMOUNT' => $arFields['OPPORTUNITY'],
                'ASSIGNED' => $arFields['ASSIGNED_BY_ID'],
                'UF_CRM_SYNC' => 1
            ]
        );

        \App\Debug\Mylog::addLog($res, 'Crm-res', '', __FILE__, __LINE__);
        // IBLOCK = 20 
    }

    public static function onDealBeforeDelete(&$id)
    {
        if (!Loader::includeModule('iblock'))  return true;

        $elementId = self::getZayyavkaByDealId($id);

        \App\Debug\Mylog::addLog($elementId, 'Crm-BD-elementId', '', __FILE__, __LINE__);

        if ($elementId) {
            global $APPLICATION;
            // $e = new CAdminException();
            // $e->AddMessage(
            //     array(
            //         "text" => 'Невозможно удалить сделку, так как она привязана к <a href="/services/lists/20/element/0/' . $elementId . '/?list_section_id=">заявке</a>',
            //     )
            // );
            $arFields['RESULT_MESSAGE'] = 'Невозможно удалить сделку, так как она привязана к <a href="/services/lists/20/element/0/' . $elementId . '/?list_section_id=">заявке</a>';
            $arFields['ERROR'] = 'Невозможно удалить сделку, так как она привязана к <a href="/services/lists/20/element/0/' . $elementId . '/?list_section_id=">заявке</a>';
            // $APPLICATION->throwException($e);
            $APPLICATION->throwException(
                'Невозможно удалить сделку, так как она привязана к <a href="/services/lists/20/element/0/' . $elementId['ID'] . '/?list_section_id=">заявке</a>'
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
