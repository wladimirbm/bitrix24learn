<?php

namespace Otus\Orm;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Fields\Relations\Reference;

class ProceduresElementTable extends DataManager
{
    public static function getTableName()
    {
        return 'b_iblock_element';
    }

    public static function getMap()
    {
        return [
         
            (new Fields\IntegerField('ID'))
                ->configurePrimary(true)
                ->configureAutocomplete(true),
            
            (new Fields\IntegerField('IBLOCK_ID'))
                ->configureRequired(true),
            
            (new Fields\StringField('NAME'))
                ->configureRequired(true)
                ->configureTitle('Название'),
            
            (new Fields\StringField('CODE'))
                ->configureTitle('Символьный код'),
            
            (new Fields\StringField('ACTIVE'))
                ->configureTitle('Активность'),
            
            (new Fields\DatetimeField('ACTIVE_FROM'))
                ->configureTitle('Дата активности'),
            
            (new Fields\IntegerField('SORT'))
                ->configureTitle('Сортировка'),
            

            new Reference(
                'ELEMENTS',
                ProceduresTable::class,
                ['=this.ID' => 'ref.ID']
            ),
            
            // (new Fields\Relations\ManyToMany('ASSISTENTS', AssistentsTable::class))
            //     ->configureMediatorTableName('otus_procedures_assistent')
            //     ->configureLocalPrimary('ID', 'PROCEDURE_ID')
            //     ->configureRemotePrimary('ID', 'ASSISTENT_ID')
        ];
    }
}