<?php
namespace Otus\Orm;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\DateField;

/**
 * Class ElementPropS16Table
 * 
 * Fields:
 * <ul>
 * <li> IBLOCK_ELEMENT_ID int mandatory
 * <li> PROPERTY_64 text optional
 * <li> PROPERTY_65 text optional
 * <li> PROPERTY_66 text optional
 * <li> PROPERTY_67 int optional
 * <li> PROPERTY_68 text optional
 * <li> PROPERTY_69 text optional
 * </ul>
 *
 * @package Bitrix\Iblock
 **/

class DoctorsTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iblock_element_prop_s16';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'IBLOCK_ELEMENT_ID' => (new IntegerField('IBLOCK_ELEMENT_ID',
					[]
				))->configureTitle(Loc::getMessage('ELEMENT_PROP_S16_ENTITY_IBLOCK_ELEMENT_ID_FIELD'))
						->configurePrimary(true)
			,
			'LASTNAME' => (new TextField('LASTNAME'))
                ->configureTitle(Loc::getMessage('ELEMENT_PROP_S16_ENTITY_PROPERTY_64_FIELD'))
                ->configureColumnName('PROPERTY_64')
			,
			'FIRSTNAME' => (new TextField('FIRSTNAME'))
                ->configureTitle(Loc::getMessage('ELEMENT_PROP_S16_ENTITY_PROPERTY_65_FIELD'))
                ->configureColumnName('PROPERTY_65')
			,
			'MIDDLENAME' => (new TextField('MIDDLENAME'))
                ->configureTitle(Loc::getMessage('ELEMENT_PROP_S16_ENTITY_PROPERTY_66_FIELD'))
                ->configureColumnName('PROPERTY_66')
			,
			'DUTY_ID' => (new IntegerField('DUTY_ID'))
                ->configureTitle(Loc::getMessage('ELEMENT_PROP_S16_ENTITY_PROPERTY_67_FIELD'))
                ->configureColumnName('PROPERTY_67')
			,
			'PROCEDURES_ID' => (new TextField('PROCEDURES_ID'))
                ->configureTitle(Loc::getMessage('ELEMENT_PROP_S16_ENTITY_PROPERTY_68_FIELD'))
                ->configureColumnName('PROPERTY_68')
			,
			'BIRTHDAY' => (new DateField('BIRTHDAY'))
                ->configureTitle(Loc::getMessage('ELEMENT_PROP_S16_ENTITY_PROPERTY_69_FIELD'))
                ->configureColumnName('PROPERTY_69')
			,
		];
	}
}
