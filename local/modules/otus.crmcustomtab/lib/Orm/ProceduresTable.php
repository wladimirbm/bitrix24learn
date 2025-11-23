<?php

namespace Otus\Crmcustomtab\Orm;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Relations\ManyToMany;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\DB\SqlExpression;
use CIBlockElement;
use Models\Lists;
use Models\Lists\DoctorProceduresPropertyValuesTable as ProceduresPropertyValuesTable;

Loc::loadMessages(__FILE__);

class ProceduresTable extends DataManager
{

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'mod_otusdoctorgrid_procedures';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{

		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete()
                ->configureTitle(Loc::getMessage('OTUS_CRMCUSTOMTAB_PROCEDURE_TABLE_ID')),

			(new StringField('NAME'))
				->configureRequired()
				->configureSize(100)
                ->configureTitle(Loc::getMessage('OTUS_CRMCUSTOMTAB_PROCEDURE_TABLE_NAME')),

			(new IntegerField('PRICE'))
                ->configureTitle(Loc::getMessage('OTUS_CRMCUSTOMTAB_PROCEDURE_TABLE_PRICE')),


			'DOCTORS' => (new ManyToMany('DOCTORS', DoctorsTable::class))
				->configureTableName('mod_otusdoctorgrid_doctor_procedure')
				->configureLocalPrimary('ID', 'PROCEDURE_ID')
				->configureRemotePrimary('ID', 'DOCTOR_ID')
				->configureTitle(Loc::getMessage('OTUS_CRMCUSTOMTAB_PROCEDURE_TABLE_DOCTORS')),
		];
	}
}
