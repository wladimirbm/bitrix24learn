<?php

namespace Otus\Orm;

use Bitrix\Main\ORM\DataManager;
use Bitrix\Main\ORM\Fields;

class ProceduresAssistentTable extends DataManager
{
    public static function getTableName()
    {
        return 'otus_procedures_assistent';
    }

    public static function getMap()
    {
        return [
            (new Fields\IntegerField('PROCEDURE_ID'))
                ->configurePrimary(true),
            
            (new Fields\IntegerField('ASSISTENT_ID'))
                ->configurePrimary(true),
            
            // Ссылка на процедуру
            new Fields\Relations\Reference(
                'PROCEDURE',
                ProceduresTable::class,
                ['=this.PROCEDURE_ID' => 'ref.ID']
            ),
            
            // Ссылка на ассистента
            new Fields\Relations\Reference(
                'ASSISTENT',
                AssistentsTable::class,
                ['=this.ASSISTENT_ID' => 'ref.ID']
            )
        ];
    }
}