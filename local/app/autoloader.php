<?php 
/*
if (file_exists(__DIR__. '/debug/mylog.php')) {
    require_once(__DIR__. '/debug/mylog.php');
} else die(__DIR__. '/debug/mylog.php');
*/

spl_autoload_register(function ($className) {
    $classPath = str_replace('\\', '/', $className);
    $file = __DIR__."/$classPath.php";
    //pr( $file);
    if (file_exists($file)) {
        include_once $file;
    }
});
