<?php

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Otus\Crmcustomtab\Orm\DoctorsTable;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Query\Result;

Loader::includeModule('otus.crmcustomtab');
class DoctorGrid extends \CBitrixComponent implements Controllerable
{
    public function configureActions(): array
    {
        return [];
    }

    private function getElementActions(): array
    {
        return [];
    }

    private function getHeaders(): array
    {
        return [
            [
                'id' => 'ID',
                'name' => 'ID',
                'sort' => 'ID',
                'default' => true,
            ],
            [
                'id' => 'LASTNAME',
                'name' => Loc::getMessage('DOCTOR_GRID_DOCTOR_LASTNAME_LABEL'),
                'sort' => 'LASTNAME',
                'default' => true,
            ],
            [
                'id' => 'FIRSTNAME',
                'name' => Loc::getMessage('DOCTOR_GRID_DOCTOR_FIRSTNAME_LABEL'),
                'sort' => 'FIRSTNAME',
                'default' => true,
            ],
            [
                'id' => 'MIDDLENAME',
                'name' => Loc::getMessage('DOCTOR_GRID_DOCTOR_MIDDLENAME_LABEL'),
                'sort' => 'MIDDLENAME',
                'default' => true,
            ],
            [
                'id' => 'BIRTHDAY',
                'name' => Loc::getMessage('DOCTOR_GRID_DOCTOR_BIRTHDAY_LABEL'),
                'sort' => 'BIRTHDAY',
                'default' => true,
            ],
            [
                'id' => 'DUTY',
                'name' => Loc::getMessage('DOCTOR_GRID_DOCTOR_DUTY_LABEL'),
                'sort' => 'DUTY',
                'default' => true,
            ],
            [
                'id' => 'PROCEDURES',
                'name' => Loc::getMessage('DOCTOR_GRID_DOCTOR_PROCEDURE_LABEL'),
                'sort' => 'PROCEDURES',
                'default' => true,
            ],

        ];
    }

    public function executeComponent(): void
    {
        $this->prepareGridData();
        $this->includeComponentTemplate();
    }

    private function prepareGridData(): void
    {
        $this->arResult['HEADERS'] = $this->getHeaders();
        $this->arResult['FILTER_ID'] = 'DOCTOR_GRID';

        $gridOptions = new GridOptions($this->arResult['FILTER_ID']);
        $navParams = $gridOptions->getNavParams();

        $nav = new PageNavigation($this->arResult['FILTER_ID']);
        $nav->allowAllRecords(true)
            ->setPageSize($navParams['nPageSize'])
            ->initFromUri();

        $filterOption = new FilterOptions($this->arResult['FILTER_ID']);
        $filterData = $filterOption->getFilter([]);
        $filter = $this->prepareFilter($filterData);


        $sort = $gridOptions->getSorting([
            'sort' => [
                'ID' => 'DESC',
            ],
            'vars' => [
                'by' => 'by',
                'order' => 'order',
            ],
        ]);

        $doctorIdsQuery = DoctorsTable::query()
            ->setSelect(['ID'])
            ->setFilter($filter)
            ->setLimit($nav->getLimit())
            ->setOffset($nav->getOffset())
            ->setOrder($sort['sort']);

        $countQuery = DoctorsTable::query()
            ->setSelect(['ID'])
            ->setFilter($filter);
        $nav->setRecordCount($countQuery->queryCountTotal());

        $doctorIds = array_column($doctorIdsQuery->exec()->fetchAll(), 'ID');

        if (!empty($doctorIds)) {
            $doctors = DoctorsTable::getList([
                'filter' => ['ID' => $doctorIds] + $filter,
                'select' => [
                    'ID',
                    'FIRSTNAME',
                    'LASTNAME',
                    'MIDDLENAME',
                    'BIRTHDAY',
                    'PROCEDURE_ID' => 'PROCEDURES.ID',
                    'PROCEDURE_NAME' => 'PROCEDURES.NAME',
                    'PROCEDURE_PRICE' => 'PROCEDURES.PRICE',
                    //'DUTY_ID' => 'DUTY_ID',
                    'DUTY_NAME' => 'DUTY.NAME',
                ],
                'order' => $sort['sort'],
            ]);

            $this->arResult['GRID_LIST'] = $this->prepareGridList($doctors);
        } else {
            $this->arResult['GRID_LIST'] = [];
        }

        $this->arResult['NAV'] = $nav;
        $this->arResult['UI_FILTER'] = $this->getFilterFields();
    }

    private function prepareFilter(array $filterData): array
    {
        $filter = [];

        if (!empty($filterData['LASTNAME'])) {
            $filter['%LASTNAME'] = $filterData['LASTNAME'];
        }
        if (!empty($filterData['FIRSTNAME'])) {
            $filter['%FIRSTNAME'] = $filterData['FIRSTNAME'];
        }
        if (!empty($filterData['MIDDLENAME'])) {
            $filter['%MIDDLENAME'] = $filterData['MIDDLENAME'];
        }
        if (!empty($filterData['PROCEDURE'])) {
            $filter['%PROCEDURE.NAME'] = $filterData['PROCEDURE'];
        }
        if (!empty($filterData['DUTY'])) {
            $filter['%DUTY.NAME'] = $filterData['DUTY'];
        }
        if (!empty($filterData['PRICE_from'])) {
            $filter['>=PROCEDURE_PRICE'] = $filterData['PRICE_from'];
        }

        if (!empty($filterData['PRICE_to'])) {
            $filter['>=PROCEDURE_PRICE'] = $filterData['PRICE_to'];
        }

        if (!empty($filterData['BIRTHDAY_from'])) {
            $filter['>=BIRTHDAY'] = $filterData['BIRTHDAY_from'];
        }

        if (!empty($filterData['BIRTHDAY_to'])) {
            $filter['<=BIRTHDAY'] = $filterData['BIRTHDAY_to'];
        }

        if (!empty($filterData['FIND'])) {
            $filter[] = array(
                'LOGIC' => 'OR',
                ['%LASTNAME' => $filterData['FIND']],
                ['%FIRSTNAME' => $filterData['FIND']],
                ['%MIDDLENAME' => $filterData['FIND']],
            );
        }


        return $filter;
    }

    private function prepareGridList(Result $doctors): array
    {
        $gridList = [];
        $groupedDoctors = [];

        while ($doctor = $doctors->fetch()) {
            $doctorId = $doctor['ID'];

            if (!isset($groupedDoctors[$doctorId])) {
                $groupedDoctors[$doctorId] = [
                    'ID' => $doctor['ID'],
                    'LASTNAME' => $doctor['LASTNAME'],
                    'FIRSTNAME' => $doctor['FIRSTNAME'],
                    'MIDDLENAME' => $doctor['MIDDLENAME'],
                    'BIRTHDAY' => $doctor['BIRTHDAY'],
                    'DUTY' => $doctor['DUTY_NAME'],
                    'PROCEDURES' => [],
                ];
            }
            if ($doctor['PROCEDURE_ID']) {
                $groupedDoctors[$doctorId]['PROCEDURES'][] = $doctor['PROCEDURE_NAME'] . ' (' . $doctor['PROCEDURE_PRICE'] . ' руб.)';
            }
        }

        foreach ($groupedDoctors as $doctor) {
            $gridList[] = [
                'data' => [
                    'ID' => $doctor['ID'],
                    'LASTNAME' => $doctor['LASTNAME'],
                    'FIRSTNAME' => $doctor['FIRSTNAME'],
                    'MIDDLENAME' => $doctor['MIDDLENAME'],
                    'BIRTHDAY' => $doctor['BIRTHDAY']->format('d.m.Y'),
                    'DUTY' => $doctor['DUTY'],
                    'PROCEDURES' => implode(', ', $doctor['PROCEDURES']),

                ],
                'actions' => $this->getElementActions(),
            ];
        }

        return $gridList;
    }

    private function getFilterFields(): array
    {
        return [
            [
                'id' => 'LASTNAME',
                'name' => Loc::getMessage('DOCTOR_GRID_DOCTOR_LASTNAME_LABEL'),
                'type' => 'string',
                'default' => true,
            ],
            [
                'id' => 'FIRSTNAME',
                'name' => Loc::getMessage('DOCTOR_GRID_DOCTOR_FIRSTNAME_LABEL'),
                'type' => 'string',
                'default' => true,
            ],
            [
                'id' => 'MIDDLENAME',
                'name' => Loc::getMessage('DOCTOR_GRID_DOCTOR_MIDDLENAME_LABEL'),
                'type' => 'string',
                'default' => true,
            ],
            [
                'id' => 'BIRTHDAY',
                'name' => Loc::getMessage('DOCTOR_GRID_DOCTOR_BIRTHDAY_LABEL'),
                'type' => 'date',
                'default' => true,
            ],
            [
                'id' => 'DUTY',
                'name' => Loc::getMessage('DOCTOR_GRID_DOCTOR_DUTY_LABEL'),
                'type' => 'string',
                'default' => true,
            ],
            [
                'id' => 'PROCEDURE',
                'name' => Loc::getMessage('DOCTOR_GRID_DOCTOR_PROCEDURE_LABEL'),
                'type' => 'string',
                'default' => true,
            ],
            [
                'id' => 'PRICE',
                'name' => Loc::getMessage('DOCTOR_GRID_DOCTOR_PROCEDURE_PRICE_LABEL'),
                'type' => 'number',
                'default' => true,
            ],
        ];
    }
}
