<?php

namespace Otus\Orm;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\DateTimeField;
use Bitrix\Main\ORM\Fields\Relations\ManyToMany;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

class AssistentsTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'otus_assistents';
    }

    public static function getMap(): array
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete(),

             (new StringField('FIRSTNAME'))
                ->configureRequired()
                ->configureSize(100),

            (new StringField('LASTNAME'))
                ->configureRequired()
                ->configureSize(100),

            (new StringField('MIDDLENAME'))
                ->configureSize(100),

            (new TextField('ABOUT')),

            (new DateField('BIRTHDAY')),

             (new IntegerField('DUTY_ID'))
                //->configureUnique()
                ,

            (new Reference('DUTY', DutyTable::class, Join::on('this.DUTY_ID', 'ref.ID')))
                ->configureJoinType('inner'),

            (new ManyToMany('DOCTORS', DoctorsTable::class))
                ->configureTableName('otus_doctor_assistent')
                ->configureLocalPrimary('ID', 'ASSISTENT_ID')
                ->configureRemotePrimary('ID', 'DOCTOR_ID'),

            //(new IntegerField('PROCEDURE_ID')),

            (new ManyToMany('PROCEDURES', ProceduresTable::class))
                ->configureTableName('otus_procedures_assistent')
                ->configureLocalPrimary('ID', 'ASSISTENT_ID')
                ->configureRemotePrimary('ID', 'PROCEDURE_ID'),

        ];
    }
}
