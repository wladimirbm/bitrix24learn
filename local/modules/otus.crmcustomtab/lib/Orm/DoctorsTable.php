<?php

namespace Otus\Crmcustomtab\Orm;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\Relations\ManyToMany;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

class DoctorsTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'mod_otusdoctorgrid_doctors';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap() // добавить оратную связь с ассистентом
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete()
                ->configureTitle(Loc::getMessage('OTUS_CRMCUSTOMTAB_DOCTOR_TABLE_ID')),

			(new StringField('FIRSTNAME'))
				->configureRequired()
				->configureSize(100)
                ->configureTitle(Loc::getMessage('OTUS_CRMCUSTOMTAB_DOCTOR_TABLE_FIRSTNAME')),

			(new StringField('LASTNAME'))
				->configureRequired()
				->configureSize(100)
                ->configureTitle(Loc::getMessage('OTUS_CRMCUSTOMTAB_DOCTOR_TABLE_LASTNAME')),

			(new StringField('MIDDLENAME'))
				->configureSize(100)
                ->configureTitle(Loc::getMessage('OTUS_CRMCUSTOMTAB_DOCTOR_TABLE_MIDDLENAME')),

			(new TextField('ABOUT'))
                ->configureTitle(Loc::getMessage('OTUS_CRMCUSTOMTAB_DOCTOR_TABLE_ABOUT')),

			(new DateField('BIRTHDAY'))
                ->configureTitle(Loc::getMessage('OTUS_CRMCUSTOMTAB_DOCTOR_TABLE_BIRTHDAY')),

			(new IntegerField('DUTY_ID'))
			//->configureUnique()
			,

			'DUTY' => (new Reference('DUTY', DutyTable::class, Join::on('this.DUTY_ID', 'ref.ID')))
				->configureJoinType('inner')
				->configureTitle(Loc::getMessage('OTUS_CRMCUSTOMTAB_DOCTOR_TABLE_DUTY')),

			'PROCEDURES' => (new ManyToMany('PROCEDURES', ProceduresTable::class))
				->configureTableName('mod_otusdoctorgrid_doctor_procedure')
				->configureLocalPrimary('ID', 'DOCTOR_ID')
				->configureRemotePrimary('ID', 'PROCEDURE_ID')
				->configureTitle(Loc::getMessage('OTUS_CRMCUSTOMTAB_DOCTOR_TABLE_PROCEDURES')),

		];
	}
}
