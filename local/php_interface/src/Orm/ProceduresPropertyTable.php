<?php

namespace Otus\Orm;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;

class ProceduresPropertyTable extends DataManager
{
    public static function getTableName()
    {
        return 'b_iblock_element_property';
    }

    public static function getMap()
    {
        return [
            (new Fields\IntegerField('ID'))
                ->configurePrimary(true)
                ->configureAutocomplete(true),
            
            (new Fields\IntegerField('IBLOCK_PROPERTY_ID')),
            
            (new Fields\IntegerField('IBLOCK_ELEMENT_ID')),
            
            (new Fields\StringField('VALUE')),
            
            (new Fields\StringField('VALUE_TYPE')),
            
            (new Fields\IntegerField('VALUE_ENUM')),
            
            (new Fields\StringField('VALUE_NUM')),
            
            // Ссылка на элемент
            new Fields\Relations\Reference(
                'ELEMENT',
                ProceduresTable::class,
                ['=this.IBLOCK_ELEMENT_ID' => 'ref.ID']
            ),
            
            // Ссылка на описание свойства
            new Fields\Relations\Reference(
                'PROPERTY',
                \Bitrix\Iblock\PropertyTable::class,
                ['=this.IBLOCK_PROPERTY_ID' => 'ref.ID']
            )
        ];
    }
}