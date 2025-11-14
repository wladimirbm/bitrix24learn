<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

$arComponentDescription = [
    'NAME' => 'Показ курса валют', // Loc::getMessage('OTUS_SHOWCURRENCY_NAME')
    'DESCRIPTION' => 'Выводит текущий курс выбранной валюты', // Loc::getMessage('OTUS_SHOWCURRENCY_DESCRIPTION')
    'ICON' => '/images/icon.gif',
    'CACHE_PATH' => 'Y',
    'PATH' => [
        'ID' => 'otus',
        'NAME' => 'OTUS',
        'CHILD' => [
            'ID' => 'show',
            'NAME' => 'Отображение валюты'
        ]
    ],
    'COMPLEX' => 'N'
];