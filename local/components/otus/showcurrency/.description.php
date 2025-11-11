<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = [
    'NAME' => 'Показ курса валют',
    'DESCRIPTION' => 'Выводит текущий курс выбранной валюты',
    'ICON' => '/images/icon.gif',
    'CACHE_PATH' => 'Y',
    'PATH' => [
        'ID' => 'otus',
        'NAME' => 'OTUS',
        'CHILD' => [
            'ID' => 'content',
            'NAME' => 'Отображение валюты'
        ]
    ],
    'COMPLEX' => 'N'
];