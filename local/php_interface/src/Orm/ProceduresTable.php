<?php

namespace Otus\Orm;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\DB\SqlExpression;
use CIBlockElement;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;

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

	protected static ?array $properties = null;
	protected static ?CIBlockElement $iblockElement = null;

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
			'IBLOCK_ELEMENT_ID' => (new IntegerField(
				'IBLOCK_ELEMENT_ID',
				[]
			))->configureTitle(Loc::getMessage('ELEMENT_PROP_S18_ENTITY_IBLOCK_ELEMENT_ID_FIELD'))
				->configurePrimary(true),
		];

		$multipleValuesTableClass = static::getMultipleValuesTableClass();
		static::initMultipleValuesTableClass();


		foreach (static::getProperties() as $property) {
			if ($property['MULTIPLE'] === 'Y') {
				$map[$property['CODE']] = new ExpressionField(
					$property['CODE'],
					sprintf(
						'(select group_concat(`VALUE` SEPARATOR "\0") as VALUE from %s as m where m.IBLOCK_ELEMENT_ID = %s and m.IBLOCK_PROPERTY_ID = %d)',
						static::getTableNameMulti(),
						'%s',
						$property['ID']
					),
					['IBLOCK_ELEMENT_ID'],
					['fetch_data_modification' => [static::class, 'getMultipleFieldValueModifier']]
				);

				if ($property['USER_TYPE'] === 'EList') {
					$map[$property['CODE'] . '_ELEMENT_NAME'] = new ExpressionField(
						$property['CODE'] . '_ELEMENT_NAME',
						sprintf(
							'(select group_concat(e.NAME SEPARATOR "\0") as VALUE from %s as m join b_iblock_element as e on m.VALUE = e.ID where m.IBLOCK_ELEMENT_ID = %s and m.IBLOCK_PROPERTY_ID = %d)',
							static::getTableNameMulti(),
							'%s',
							$property['ID']
						),
						['IBLOCK_ELEMENT_ID'],
						['fetch_data_modification' => [static::class, 'getMultipleFieldValueModifier']]
					);
				}

				$map[$property['CODE'] . '|SINGLE'] = new ReferenceField(
					$property['CODE'] . '|SINGLE',
					$multipleValuesTableClass,
					[
						'=this.IBLOCK_ELEMENT_ID' => 'ref.IBLOCK_ELEMENT_ID',
						'=ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?i', $property['ID'])
					]
				);

				continue;
			}

			if ($property['PROPERTY_TYPE'] == PropertyTable::TYPE_NUMBER) {
				$map[$property['CODE']] = new IntegerField("PROPERTY_{$property['ID']}");
			} elseif ($property['USER_TYPE'] === 'Date') {
				$map[$property['CODE']] = new DatetimeField("PROPERTY_{$property['ID']}");
			} else {
				$map[$property['CODE']] = new StringField("PROPERTY_{$property['ID']}");
			}

			if ($property['PROPERTY_TYPE'] === 'E' && ($property['USER_TYPE'] === 'EList' || is_null($property['USER_TYPE']))) {
				$map[$property['CODE'] . '_ELEMENT'] = new ReferenceField(
					$property['CODE'] . '_ELEMENT',
					ElementTable::class,
					["=this.{$property['CODE']}" => 'ref.ID']
				);
			}
		}

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
