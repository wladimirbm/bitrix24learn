<?php

namespace App\Debug;

use Bitrix\Main\Diag\FileExceptionHandlerLog;
use Bitrix\Main\Diag\ExceptionHandlerFormatter;

class Mylog extends FileExceptionHandlerLog
{

    /** Add data to log file:
     * $data - array() - data for write to file
     * $namedata - string - name of var
     * $logfile - string - other name  for log file
     * $fromfile - string - name of file, can be __FILE__
     * $fromline - string - number of line, can be __LINE__
     */
    public static function addLog($data, $namedata = null, $logfile = null, $fromfile = null, $fromline = null)
    {
        if (empty($logfile))
            $logfile = "mylog_" . date('Y-m-d') . ".log";
        else if (substr($logfile, 3) != "log")
            $logfile .= $logfile . ".log";

        $log = date('[Y-m-d H:i:s] ');
        if (!empty($fromfile)) {
            $log .= "- File:".$fromfile;
        }
        if (!empty($fromline)) {
            $log .= " - Line:" . $fromline;
        }
        $log .= "\n";
        if (!empty($namedata)) {
            $log .= $namedata . ":\n";
        }

        $log .= print_r($data, true) . "\n\n";

        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/logs")) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . "/logs", BX_DIR_PERMISSIONS, true);
        }

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/logs/" . $logfile, $log, FILE_APPEND);

        return;
    }

    public static function ClearDefLog()
    {
        $logfile = "mylog_" . date('Y-m-d') . ".log";
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/logs/" . $logfile, '');
        return;
    }

    public static function ClearDefException()
    {
        $logfile = "exception_" . date('Y-m-d') . ".log";
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/logs/" . $logfile, '');
        return;
    }
}
