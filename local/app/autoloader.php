<?php 
if (file_exists(__DIR__. '/debug/mylog.php')) {
    require_once(__DIR__. '/debug/mylog.php');
} else die(__DIR__. '/debug/mylog.php');

if (file_exists(__DIR__. '/Models/AbstractIblockPropertyValuesTable.php')) {
    require_once(__DIR__. '/Models/AbstractIblockPropertyValuesTable.php');
} else die(__DIR__. '/Models/AbstractIblockPropertyValuesTable.php');

if (file_exists(__DIR__. '/Models/Lists/DoctorsPropertyValuesTable.php')) {
    require_once(__DIR__. '/Models/Lists/DoctorsPropertyValuesTable.php');
} else die(__DIR__. '/Models/Lists/DoctorsPropertyValuesTable.php');

if (file_exists(__DIR__. '/Models/Lists/DoctorDutyPropertyValuesTable.php')) {
    require_once(__DIR__. '/Models/Lists/DoctorDutyPropertyValuesTable.php');
} else die(__DIR__. '/Models/Lists/DoctorDutyPropertyValuesTable.php');

/*
spl_autoload_register(function ($className) {
    $classPath = str_replace('\\', '/', $className);
    $file = __DIR__."/$classPath.php";
    print_r( $file);
    if (file_exists($file)) {
        include_once $file;
    }
});

*/