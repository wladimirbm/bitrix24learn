<?php

namespace Otus\Crmcustomtab\Orm;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Relations\Reference;

Loc::loadMessages(__FILE__);

class DutyTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'mod_otusdoctorgrid_duty';
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
                ->configureTitle(Loc::getMessage('OTUS_CRMCUSTOMTAB_DUTY_TABLE_ID')),

			(new StringField('NAME'))
				->configureRequired()
				->configureSize(100)
                ->configureTitle(Loc::getMessage('OTUS_CRMCUSTOMTAB_DUTY_TABLE_NAME')),


		];
	}
}
