<?php

namespace Otus\Diag;

use Bitrix\Main;
use Psr\Log;
use Bitrix\Main\Diag\FileExceptionHandlerLog;
use Bitrix\Main\Diag\ExceptionHandlerFormatter;

class FileExceptionHandlerLogCustom extends FileExceptionHandlerLog
{
	//const MAX_LOG_SIZE = 1000000;
	//const DEFAULT_LOG_FILE = "/logs/exception.log";

	private $level;

	/** @var Log\LoggerInterface */
	//protected $logger;

	/**
	 * @param \Throwable $exception
	 * @param int $logType
	 */
	public function write($exception, $logType)
	{

		$text = ExceptionHandlerFormatter::format($exception, false, $this->level);

		$context = [
			'type' => static::logTypeToString($logType),
		];

		$logLevel = static::logTypeToLevel($logType);

		$message = "OTUS: [{date}] - Host: {host} - {type} - {$text}\n";
		$message .= "-----\n";
		
		ob_start();
		debug_print_backtrace();
		$backtrace = ob_get_clean();
		
		$message .= print_r($backtrace) . "\n";
		$message .= "-----\n\n";

		$this->logger->log($logLevel, $message, $context);
	}
}
