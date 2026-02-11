<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

// Автоподгрузка нашего класса контроллера
Loader::registerAutoLoadClasses(null, [
    '\\Local\\Components\\CarSelector\\CarSelectorController' => '/local/components/custom/car.selector/class.php',
]);

// Регистрируем контроллер в системе
$arResult = [
    'controllers' => [
        'value' => [
            '\\Local\\Components\\CarSelector\\CarSelectorController' => [
                'className' => '\\Local\\Components\\CarSelector\\CarSelectorController'
            ]
        ],
        'readonly' => true
    ]
];

return $arResult;