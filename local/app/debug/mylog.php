<?php

namespace App\Diag;

use Bitrix\Main\Diag\FileExceptionHandlerLog;
use Bitrix\Main\Diag\ExceptionHandlerFormatter;

class Mylog extends FileExceptionHandlerLog
{

    public static function addLog($data, $namedata = null, $logfile = null, $fromfile = null, $fromline = null)
    {
        if (empty($logfile))
            $logfile = "mylog_" . date('Y-m-d') . ".log";
        else if (substr($logfile, 3) != "log")
            $logfile .= $logfile . ".log";

        $log = date('[Y-m-d H:i:s] ');
        if (empty($fromfile)) {
            $log .= $fromfile;
        }
        if (empty($fromline)) {
            $log .= " : " . $fromline;
        }
        $log .= "\n";
        if (empty($namedata)) {
            $log .= $namedata . "\n";
        }

        $log .= print_r($data, true) . "\n\n";

        file_put_contents("/logs/" . $logfile, $log);

        return;
    }

    public static function ClearDefLog()
    {
        $logfile = "mylog_" . date('Y-m-d') . ".log";
        file_put_contents("/logs/" . $logfile, '');
    }
    
    public static function ClearDefException()
    {
        $logfile = "exception_" . date('Y-m-d') . ".log";
        file_put_contents("/logs/" . $logfile, '');
    }
} 
