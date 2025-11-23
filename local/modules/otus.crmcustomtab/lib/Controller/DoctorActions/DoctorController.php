<?php
namespace Otus\Crmcustomtab\Controller\DoctorActions;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Response\Component;
use Otus\Crmcustomtab\Orm\DoctorsTable;

class DoctorsController extends Controller
{
    public function configureActions(): array
    {
        return [
            'createTestElement' => [
                'prefilters' => [],
            ],
            'showNewGrid' => [
                'prefilters' => [],
            ],
        ];
    }

    public function createTestElementAction(): array
    {
        try {
            $addResult = DoctorsTable::add([
                'FIRSTNAME' => 'Имя ' . rand(1000, 9999),
                'LASTNAME' => 'Фамилия ' . rand(1000, 9999),
                'MIDDLENAME' => 'Отчество ' . rand(1000, 9999),
                'BIRTHDAY' => new \Bitrix\Main\Type\Date(rand(1970, date('Y')).'-'.rand(1, 12).'-'.rand(1, 29)),
                'DUTY_ID' => rand(1, 10),
                //'BIRTHDAY' => new \Bitrix\Main\Type\DateTime(),
            ]);

            if ($addResult->isSuccess()) {
                $result['DOCTOR_ID'] = $addResult->getId();
            } else {
                $this->errorCollection->add($addResult->getErrorMessages());
                return [];
            }
        } catch (\Exception $e) {
            $this->errorCollection->add([new Error($e->getMessage())]);
            return [];
        }

        return $result;
    }

    public function showNewGridAction(): Component
    {
        return new Component('bitrix:news.list', '', [
            'IBLOCK_ID' => 3,
        ]);
    }
}
