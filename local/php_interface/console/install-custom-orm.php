<?php
if (php_sapi_name() != 'cli')
{
    die();
}

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);
define("BX_NO_ACCELERATOR_RESET", true);
define("BX_CRONTAB", true);
define("STOP_STATISTICS", true);
define("NO_AGENT_STATISTIC", "Y");
define("DisableEventsCheck", true);
define("NO_AGENT_CHECK", true);

//$_SERVER['DOCUMENT_ROOT'] = realpath('/home/bitrix/www');
echo $_SERVER['DOCUMENT_ROOT'];
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Entity\Base;
use Bitrix\Main\Application;
use Otus\Orm\AssistentsTable;



$entities = [
    AssistentsTable::class,
];

foreach ($entities as $entity) {
    if (!Application::getConnection($entity::getConnectionName())->isTableExists($entity::getTableName())) {
        Base::getInstance($entity)->createDbTable();
    }
}

$connection = Application::getConnection();

$tableName = 'otus_assistents';

$connection->queryExecute("
	insert into {$tableName} 
	(FIRSTNAME, LASTNAME, MIDDLENAME, ABOUT, BIRTHDAY, DUTY_ID) 
	values 
	(1, 'Первый', 'Саша', '', 'абырвалг 1', '1990-12-12', 139),
	(2, 'Второй', 'Миша', '', 'абырвалг 2', '1991-1-12', 140),
	(3, 'Третья', 'Аня', '', 'абырвалг 3', '1992-2-12', 141),
	(4, 'Четвертый', 'Коля', '', 'абырвалг 4', '193-3-12', 142)
");


$tableName = 'otus_doctor_assistent';

if (!$connection->isTableExists($tableName)) {
    $connection->queryExecute("
		CREATE TABLE {$tableName} (
			ASSISTENT_ID int NOT NULL,
			DOCTOR_ID int NOT NULL,
			PRIMARY KEY (ASSISTENT_ID, DOCTOR_ID)
		)
	");

 $connection->queryExecute("
		insert into {$tableName} 
		(ASSISTENT_ID, DOCTOR_ID) 
		values 
		(1,307),
		(2,308),
		(3,309)
	");
}

$tableName = 'otus_procedures_assistent';

if (!$connection->isTableExists($tableName)) {
    $connection->queryExecute("
		CREATE TABLE {$tableName} (
			ASSISTENT_ID int NOT NULL,
			PROCEDURE_ID int NOT NULL,
			PRIMARY KEY (ASSISTENT_ID, PROCEDURE_ID)
		)
	");

	 $connection->queryExecute("
		insert into {$tableName} 
		(ASSISTENT_ID, PROCEDURE_ID) 
		values 
		(1,300),
		(2,301),
		(3,302)
	");
}
