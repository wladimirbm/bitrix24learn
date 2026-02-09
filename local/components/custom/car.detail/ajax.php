<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$carId = (int)$_POST['car_id'];
if (!$carId || !check_bitrix_sessid()) {
    echo 'Ошибка запроса';
    exit;
}

$APPLICATION->IncludeComponent(
    'custom:car.detail',
    '',
    ['CAR_ID' => $carId],
    false
);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
?>