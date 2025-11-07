<?php

namespace Otus\Orm;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\Reference;
use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\Relations\ManyToMany;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\DB\SqlExpression;
use CIBlockElement;
use Models\Lists;
use Models\Lists\DoctorProceduresPropertyValuesTable as ProceduresPropertyValuesTable; 

/**
 * Class ElementPropS18Table
 * 
 * Fields:
 * <ul>
 * <li> IBLOCK_ELEMENT_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Iblock
 **/

class ProceduresTable extends DataManager
{

	const IBLOCK_ID = 18;

	// protected static ?array $properties = null;
	// protected static ?CIBlockElement $iblockElement = null;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iblock_element_prop_s18';
	}


	/**
	 * @return string
	 */
	public static function getTableNameMulti(): string
	{
		return 'b_iblock_element_prop_m' . static::IBLOCK_ID;
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{


		$map = [
			'ID' => (new IntegerField(
				'IBLOCK_ELEMENT_ID',
				[]
			))->configureTitle(Loc::getMessage('ELEMENT_PROP_S18_ENTITY_IBLOCK_ELEMENT_ID_FIELD'))
				->configurePrimary(true),

			(new Fields\StringField('NAME'))
                ->configureRequired(true)
                ->configureTitle('Название процедуры'),
			// Связь со свойствами инфоблока
            //'PROPERTIES' => (new Fields\Relations\OneToMany('PROPERTIES', ProceduresPropertyTable::class, 'ELEMENT')),
				
			'ASSISTENTS' => (new ManyToMany('ASSISTENTS', AssistentsTable::class))
				->configureTableName('otus_procedures_assistent')
				->configureLocalPrimary('ID', 'PROCEDURE_ID')
				->configureRemotePrimary('ID', 'ASSISTENT_ID'),
		];

		return $map;
	}

}
