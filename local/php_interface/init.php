<?php
//require_once( $_SERVER['DOCUMENT_ROOT']. '/vendor/autoload.php');
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once(__DIR__ . '/../../vendor/autoload.php');
}

if (file_exists(__DIR__ . '/../app/autoloader.php')) {
    require_once(__DIR__ . '/../app/autoloader.php');
}
// if (file_exists(__DIR__ . '/../app/autoloader.php')) {
//     require_once(__DIR__ . '/../app/autoloader.php');
// }

if (file_exists(__DIR__ . '/classes/Dadata.php')) {
    include_once __DIR__ . '/classes/Dadata.php';
}
if (file_exists(__DIR__ . '/classes/UserTypes/BookingProcedureType.php')) {
    include_once __DIR__ . '/classes/UserTypes/BookingProcedureType.php';
}

if (file_exists(__DIR__ . '/events.php')) {
    require_once(__DIR__ . '/events.php');
}

if (file_exists(__DIR__ . '/agents/smarts.php')) {
    require_once(__DIR__ . '/agents/smarts.php');
}

// require_once $_SERVER['DOCUMENT_ROOT'] . '/../include/car_filter_init.php';

if (file_exists(__DIR__ . '/../include/car_filter_init.php')) {
    require_once(__DIR__ . '/../include/car_filter_init.php');
} else die(__DIR__ . '/../include/car_filter_init.php');