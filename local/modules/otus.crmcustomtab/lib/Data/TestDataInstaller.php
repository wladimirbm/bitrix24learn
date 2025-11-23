<?php

namespace Otus\Crmcustomtab\Data;

use Otus\Crmcustomtab\Orm\DoctorsTable;
use Otus\Crmcustomtab\Orm\ProceduresTable;
use Otus\Crmcustomtab\Orm\DutyTable;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;

class TestDataInstaller
{

    /**
     * @throws SystemException
     * @throws \Exception
     */
    public static function addProcedures()
    {

        $procedures = [
            [
                'NAME' => 'Лабораторные анализы',
                'PRICE' => RAND(5, 50) * 100,
            ],
            [
                'NAME' => 'ЭКГ и функциональная диагностика сердца',
                'PRICE' => RAND(5, 50) * 100,
            ],
            [
                'NAME' => 'УЗИ внутренних органов',
                'PRICE' => RAND(5, 50) * 100,
            ],
            [
                'NAME' => 'Флюорография и компьютерная томография',
                'PRICE' => RAND(5, 50) * 100,
            ],
            [
                'NAME' => 'Эндоскопические исследования',
                'PRICE' => RAND(5, 50) * 100,
            ],
            [
                'NAME' => 'Анализ на кровь',
                'PRICE' => RAND(5, 50) * 100,
            ],
            [
                'NAME' => 'Маммография',
                'PRICE' => RAND(5, 50) * 100,
            ],
            [
                'NAME' => 'Измерение внутриглазного давления',
                'PRICE' => RAND(5, 50) * 100,
            ],
            [
                'NAME' => 'Гинекологический осмотр',
                'PRICE' => RAND(5, 50) * 100,
            ],
            [
                'NAME' => 'Определение антигена',
                'PRICE' => RAND(5, 50) * 100,
            ],
        ];

        foreach ($procedures as $procedure) {
            ProceduresTable::add($procedure);
        }
    }

    /**
     * @throws SystemException
     * @throws \Exception
     */
    public static function addDuty()
    {
        $dutys = [
            [
                'NAME' => 'Хирург',
            ],
            [
                'NAME' => 'Стоматолог',
            ],
            [
                'NAME' => 'Акушер',
            ],
            [
                'NAME' => 'Гинеколог',
            ],
            [
                'NAME' => 'Косметолог',
            ],
            [
                'NAME' => 'Дерматовенеролог',
            ],
            [
                'NAME' => 'Анестезиолог',
            ],
            [
                'NAME' => 'Терапевт',
            ],
            [
                'NAME' => 'Онколог',
            ],
            [
                'NAME' => 'Педиатр',
            ],
        ];

        foreach ($dutys as $duty) {
            DutyTable::add($duty);
        }
    }

    public static function addDoctors(): void
    { 
        //str_pad(RAND(1, 29), 2, "0", STR_PAD_LEFT)
        //str_pad(RAND(1, 12), 2, "0", STR_PAD_LEFT)
        $doctors = [
            [
                'LASTNAME' => 'Первый',
                'FIRSTNAME' => 'Миша',
                'MIDDLENAME' => 'Николаевич',
                'BIRTHDAY' => str_pad(RAND(1, 29), 2, "0", STR_PAD_LEFT) . '.' . str_pad(RAND(1, 12), 2, "0", STR_PAD_LEFT) . '.' . RAND(1960, 2000),
                'ABOUT' => 'Хороший человек.',
                'DUTY_ID' => RAND(1, 10),
                'PROCEDURES' => [RAND(1, 10), RAND(1, 10)],
            ],
            [
                'LASTNAME' => 'Второй',
                'FIRSTNAME' => 'Иван',
                'MIDDLENAME' => 'Николаевич',
                'BIRTHDAY' => str_pad(RAND(1, 29), 2, "0", STR_PAD_LEFT) . '.' . str_pad(RAND(1, 12), 2, "0", STR_PAD_LEFT) . '.' . RAND(1960, 2000),
                'ABOUT' => 'Хороший человек.',
                'DUTY_ID' => RAND(1, 10),
                'PROCEDURES' => [RAND(1, 10), RAND(1, 10)],
            ],
            [
                'LASTNAME' => 'Третий',
                'FIRSTNAME' => 'Коля',
                'MIDDLENAME' => 'Николаевич',
                'BIRTHDAY' => str_pad(RAND(1, 29), 2, "0", STR_PAD_LEFT) . '.' . str_pad(RAND(1, 12), 2, "0", STR_PAD_LEFT) . '.' . RAND(1960, 2000),
                'ABOUT' => 'Хороший человек.',
                'DUTY_ID' => RAND(1, 10),
                'PROCEDURES' => [RAND(1, 10), RAND(1, 10)],
            ],
            [
                'LASTNAME' => 'Четвернтый',
                'FIRSTNAME' => 'Толя',
                'MIDDLENAME' => 'Николаевич',
                'BIRTHDAY' => str_pad(RAND(1, 29), 2, "0", STR_PAD_LEFT) . '.' . str_pad(RAND(1, 12), 2, "0", STR_PAD_LEFT) . '.' . RAND(1960, 2000),
                'ABOUT' => 'Хороший человек.',
                'DUTY_ID' => RAND(1, 10),
                'PROCEDURES' => [RAND(1, 10), RAND(1, 10)],
            ],
            [
                'LASTNAME' => 'Пятый',
                'FIRSTNAME' => 'Семен',
                'MIDDLENAME' => 'Николаевич',
                'BIRTHDAY' => str_pad(RAND(1, 29), 2, "0", STR_PAD_LEFT) . '.' . str_pad(RAND(1, 12), 2, "0", STR_PAD_LEFT) . '.' . RAND(1960, 2000),
                'ABOUT' => 'Хороший человек.',
                'DUTY_ID' => RAND(1, 10),
                'PROCEDURES' => [RAND(1, 10), RAND(1, 10)],
            ],
            [
                'LASTNAME' => 'Шестой',
                'FIRSTNAME' => 'Петр',
                'MIDDLENAME' => 'Николаевич',
                'BIRTHDAY' => str_pad(RAND(1, 29), 2, "0", STR_PAD_LEFT) . '.' . str_pad(RAND(1, 12), 2, "0", STR_PAD_LEFT) . '.' . RAND(1960, 2000),
                'ABOUT' => 'Хороший человек.',
                'DUTY_ID' => RAND(1, 10),
                'PROCEDURES' => [RAND(1, 10), RAND(1, 10)],
            ],
            [
                'LASTNAME' => 'Седьмой',
                'FIRSTNAME' => 'Дима',
                'MIDDLENAME' => 'Николаевич',
                'BIRTHDAY' => str_pad(RAND(1, 29), 2, "0", STR_PAD_LEFT) . '.' . str_pad(RAND(1, 12), 2, "0", STR_PAD_LEFT) . '.' . RAND(1960, 2000),
                'ABOUT' => 'Хороший человек.',
                'DUTY_ID' => RAND(1, 10),
                'PROCEDURES' => [RAND(1, 10), RAND(1, 10)],
            ],
            [
                'LASTNAME' => 'Восьмая',
                'FIRSTNAME' => 'Анна',
                'MIDDLENAME' => 'Николаевич',
                'BIRTHDAY' => str_pad(RAND(1, 29), 2, "0", STR_PAD_LEFT) . '.' . str_pad(RAND(1, 12), 2, "0", STR_PAD_LEFT) . '.' . RAND(1960, 2000),
                'ABOUT' => 'Хороший человек.',
                'DUTY_ID' => RAND(1, 10),
                'PROCEDURES' => [RAND(1, 10), RAND(1, 10)],
            ],
            [
                'LASTNAME' => 'Девятая',
                'FIRSTNAME' => 'Мила',
                'MIDDLENAME' => 'Николаевич',
                'BIRTHDAY' => str_pad(RAND(1, 29), 2, "0", STR_PAD_LEFT) . '.' . str_pad(RAND(1, 12), 2, "0", STR_PAD_LEFT) . '.' . RAND(1960, 2000),
                'ABOUT' => 'Хороший человек.',
                'DUTY_ID' => RAND(1, 10),
                'PROCEDURES' => [RAND(1, 10), RAND(1, 10)],
            ],
            [
                'LASTNAME' => 'Десятая',
                'FIRSTNAME' => 'Настя',
                'MIDDLENAME' => 'Николаевич',
                'BIRTHDAY' => str_pad(RAND(1, 29), 2, "0", STR_PAD_LEFT) . '.' . str_pad(RAND(1, 12), 2, "0", STR_PAD_LEFT) . '.' . RAND(1960, 2000),
                'ABOUT' => 'Хороший человек.',
                'DUTY_ID' => RAND(1, 10),
                'PROCEDURES' => [RAND(1, 10), RAND(1, 10)],
            ],

        ];

        foreach ($doctors as $doctor) {
            $doctor['BIRTHDAY'] = DateTime::createFromText($doctor['BIRTHDAY']);
            $procIds = $doctor['PROCEDURES'];
            unset($doctor['PROCEDURES']);

            $resultAdd = DoctorsTable::add($doctor);
            if (!$resultAdd->isSuccess()) {
                throw new SystemException('Не удалось добавить тестовые данные: ' . implode(', ', $resultAdd->getErrorMessages()));
            }

            $docId = $resultAdd->getId();
            $doc = DoctorsTable::getByPrimary($docId)->fetchObject();

            if ($doc) {
                foreach ($procIds as $pId) {
                    $proc = ProceduresTable::getByPrimary($pId)->fetchObject();
                    if ($proc) {
                        $doc->addToProcedures($proc);
                    }
                }
                $doc->save();
            }

        }
    }
}
