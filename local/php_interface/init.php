<?php
//require_once( $_SERVER['DOCUMENT_ROOT']. '/vendor/autoload.php');
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once(__DIR__ . '/../../vendor/autoload.php');
}

if (file_exists(__DIR__ . '/../app/autoloader.php')) {
    require_once(__DIR__ . '/../app/autoloader.php');
}
if (file_exists(__DIR__ . '/../app/autoloader.php')) {
    require_once(__DIR__ . '/../app/autoloader.php');
}

if (file_exists(__DIR__ . '/events.php')) {
    require_once(__DIR__ . '/events.php');
}
if (file_exists(__DIR__ . '/classes/Dadata.php')) {
    include_once __DIR__ . '/classes/Dadata.php';
}
