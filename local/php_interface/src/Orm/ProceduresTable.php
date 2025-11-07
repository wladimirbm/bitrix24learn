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
use Models\Lists\DoctorProceduresPropertyValuesTable as DoctorProceduresPropertyValuesTable; 

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

			 // СВЯЗЬ СО СВОЙСТВАМИ ЧЕРЕЗ ElementPropertyTable
            (new OneToMany('PROPERTIES', ElementPropertyTable::class, 'ELEMENT'))
                ->configureJoinType('LEFT'),
            
            // СВЯЗЬ С ЗНАЧЕНИЯМИ СВОЙСТВ
            (new OneToMany('PROPERTY_VALUES', ElementPropertyValueTable::class, 'ELEMENT'))
                ->configureJoinType('LEFT'),
				
			'ASSISTENTS' => (new ManyToMany('ASSISTENTS', AssistentsTable::class))
				->configureTableName('otus_procedures_assistent')
				->configureLocalPrimary('ID', 'PROCEDURE_ID')
				->configureRemotePrimary('ID', 'ASSISTENT_ID'),
		];

		return $map;
	}

	/**
	 * @return array
	 * @throws ArgumentException
	 * @throws SystemException
	 * @throws ObjectPropertyException
	 */
	public static function getProperties(): array
	{
		if (isset(static::$properties[static::IBLOCK_ID])) {
			return static::$properties[static::IBLOCK_ID];
		}

		$dbResult = PropertyTable::query()
			->setSelect(['ID', 'CODE', 'PROPERTY_TYPE', 'MULTIPLE', 'NAME', 'USER_TYPE'])
			->where('IBLOCK_ID', static::IBLOCK_ID)
			->exec();
		while ($row = $dbResult->fetch()) {
			static::$properties[static::IBLOCK_ID][$row['CODE']] = $row;
		}

		return static::$properties[static::IBLOCK_ID] ?? [];
	}

	/**
	 * @return array
	 */
	public static function getMultipleFieldValueModifier(): array
	{
		return [fn($value) => array_filter(explode("\0", $value))];
	}

	/**
	 * @return string
	 */
	private static function getMultipleValuesTableClass(): string
	{
		$className = end(explode('\\', static::class));
		$namespace = str_replace('\\' . $className, '', static::class);
		$className = str_replace('Table', 'MultipleTable', $className);

		return $namespace . '\\' . $className;
	}

	/**
	 * @return void
	 */
	private static function initMultipleValuesTableClass(): void
	{
		$className = end(explode('\\', static::class));
		$namespace = str_replace('\\' . $className, '', static::class);
		$className = str_replace('Table', 'MultipleTable', $className);

		if (class_exists($namespace . '\\' . $className)) {
			return;
		}

		$iblockId = static::IBLOCK_ID;

		//         $php = <<<PHP
		// namespace $namespace;

		// class {$className} extends \Models\AbstractIblockPropertyMultipleValuesTable
		// {
		//     const IBLOCK_ID = {$iblockId};
		// }

		// PHP;
		//         eval($php);
	}
}
