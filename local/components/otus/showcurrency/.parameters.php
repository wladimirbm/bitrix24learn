<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Currency\CurrencyTable;

Loc::loadMessages(__FILE__);

$arCurrencies = [];
if (Loader::includeModule('currency')) {
    $dbCurrencies = CurrencyTable::getList([
        'select' => ['CURRENCY'],
        'order' => ['SORT' => 'ASC', 'CURRENCY' => 'ASC']
    ]);
    
    while ($currency = $dbCurrencies->fetch()) {
        $arCurrencies[$currency['CURRENCY']] = $currency['CURRENCY'];// . ' - ' . $currency['AMOUNT'] . ' - ' . $currency['DATE_UPDATE'];
    }
}

$arComponentParameters = [
    'PARAMETERS' => [
        'CURRENCY' => [
            'PARENT' => 'BASE',
            'NAME' => 'Валюта',   // Loc::getMessage('OTUS_SHOWCURRENCY_CURRENCY_PARAM')
            'TYPE' => 'LIST',
            'VALUES' => $arCurrencies,
            'DEFAULT' => 'USD',
            'REFRESH' => 'N'
        ],
        'CACHE_TIME' => [
            'DEFAULT' => 3600
        ]
    ]
];