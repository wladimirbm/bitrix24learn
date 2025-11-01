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

class HospitalScheduleTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'otus_hostital_schedule';
    }

    public static function getMap(): array
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete(),

            (new TextField('NAME')),

            (new DateField('BIRTHDAY')),

            (new DateTimeField('VISIT_DATE')),

            (new IntegerField('DOCTOR_ID')),

            (new Reference(
                'DOCTORS', DoctorsTable::class,
                Join::on('this.DOCTOR_ID', 'ref.IBLOCK_ELEMENT_ID')
            ))
                ->configureJoinType('inner'),


            (new TextField('PROCEDURE_ID')),

            (new TextField('CONTACT_ID')),

            (new ManyToMany('PROCEDURES', ProceduresTable::class))
                ->configureTableName('otus_schedule_procedire')
                ->configureLocalPrimary('ID', 'SCHEDULE_ID')
                ->configureRemotePrimary('ID', 'PROCEDURE_ID'),
        ];
    }
}
