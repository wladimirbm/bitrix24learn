<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Localization\Loc;

$arActivityDescription = [
    "NAME" => Loc::getMessage("CCBYINN_DESCR_NAME"),
    "DESCRIPTION" => Loc::getMessage("CCBYINN_DESCR_DESCR"),
    "TYPE" => "activity",
    "CLASS" => "CBPCreateCompanyByInn",
    "JSCLASS" => "BizProcActivity",
    "CATEGORY" => [
        "ID" => "other",
    ],
    "RETURN" => [
        "Text" => [
            "NAME" => Loc::getMessage("CCBYINN_DESCR_FIELD_TEXT"),
            "TYPE" => "string",
        ],
    ],
];