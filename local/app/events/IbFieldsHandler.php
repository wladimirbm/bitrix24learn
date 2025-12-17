<?php

namespace App\Events;

use Bitrix\Main\Loader;
use CIBlockElement;
use Bitrix\Crm\DealTable;
use CAdminException;

class IbFieldsHandler
{
    public static function onElementAfterUpdate(&$arFields)
    {
        if (!Loader::includeModule('iblock') || !Loader::includeModule('crm'))  return;

        if ($arFields['IBLOCK_ID'] == 20) {

            $element = CIBlockElement::GetList(
                [],
                ['ID' => $arFields['ID']],
                false,
                false,
                ['ID', 'IBLOCK_ID']
            )->Fetch();

            if (!$element) return;

            $propsRes = CIBlockElement::GetProperty(
                $element['IBLOCK_ID'],
                $arFields['ID'],
                [],
                [],
                ['CODE' => ['DEAL', 'AMOUNT', 'ASSIGNED', 'UF_CRM_SYNC']]
            );

            $properties = [];
            while ($prop = $propsRes->Fetch()) {
                $properties[$prop['CODE']] = $prop['VALUE'];
            }

            $dealId = $properties['DEAL'] ?? 0;
            if (!$dealId) return;

            // if (!empty($properties['UF_CRM_SYNC'])) {
            //     CIBlockElement::SetPropertyValuesEx(
            //         $arFields['ID'],
            //         $element['IBLOCK_ID'],
            //         ['UF_CRM_SYNC' => 0
            //         ]
            //     );
            //     return;
            // }

            $updDeal = [];
            if (!empty($properties['ASSIGNED'])) $updDeal['ASSIGNED_BY_ID'] = $properties['ASSIGNED'];
            else {
                global $APPLICATION;
                $e = new CAdminException();
                $e->AddMessage(
                    array(
                        "text" => 'Необходимо выбрать Ответственного',
                    )
                );
                $arFields['RESULT_MESSAGE'] = 'Необходимо выбрать Ответственного';
                $arFields['ERROR'] = 'Необходимо выбрать Ответственного';
                $APPLICATION->throwException($e);
            }
            if (!empty($properties['AMOUNT'])) $updDeal['OPPORTUNITY'] = $properties['AMOUNT'];
            else $updDeal['OPPORTUNITY'] = 0;

            if (!empty($updDeal)) {
                DealTable::Update($dealId, $updDeal);
                \App\Debug\Mylog::addLog($updDeal, 'IB-updDeal', '', __FILE__, __LINE__);
            }
        }
    }
}
