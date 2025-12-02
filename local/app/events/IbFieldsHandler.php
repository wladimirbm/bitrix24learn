<?php

namespace App\Events;

use Bitrix\Main\Loader;
use CIBlockElement;

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

            if (!empty($properties['UF_CRM_SYNC'])) {
                CIBlockElement::SetPropertyValuesEx(
                    $arFields['ID'],
                    $element['IBLOCK_ID'],
                    ['UF_CRM_SYNC' => false]
                );
                return;
            }

            CCrmDeal::Update($dealId, [
                'OPPORTUNITY' => $properties['AMOUNT'],
                'ASSIGNED_BY_ID' => $properties['ASSIGNED'],
                'UF_IB_SYNC' => true
            ]);
        }
    }
}
