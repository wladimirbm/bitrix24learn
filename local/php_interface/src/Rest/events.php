<?php

namespace Otus\Rest;

use Bitrix\Main\Application;
use Bitrix\Rest\RestException;
use Bitrix\Main\Event;
use Bitrix\Main\Localization\Loc;
use Otus\Orm\DoctorsTable;

Loc::loadMessages(__FILE__);

class Events
{
    private static $startTime;

    public static function OnRestServiceBuildDescriptionHandler(): array
    {
        return [
            'otus.doctor' => [
                // CRUD методы REST
                'otus.doctor.add' => [__CLASS__, 'add'],
                'otus.doctor.get' => [__CLASS__, 'get'],
                'otus.doctor.list' => [__CLASS__, 'list'],
                'otus.doctor.update' => [__CLASS__, 'update'],
                'otus.doctor.delete' => [__CLASS__, 'delete'],

                // исходящие вебхуки 
                \CRestUtil::EVENTS => [
                    'onAfterOtusDoctorAdd' => [
                        'main',
                        'onAfterOtusDoctorAdd',
                        [__CLASS__, 'prepareEventData']
                    ],
                    'onAfterOtusDoctorUpdate' => [
                        'main',
                        'onAfterOtusDoctorUpdate',
                        [__CLASS__, 'prepareEventData']
                    ],
                    'onAfterOtusDoctorDelete' => [
                        'main',
                        'onAfterOtusDoctorDelete',
                        [__CLASS__, 'prepareEventData']
                    ],
                ],
            ],
        ];
    }

    private static function formatResponse($data, $isList = false)
    {
        //static::$startTime = microtime(true);
        $endTime = microtime(true);
        $response = [
            'result' => $data,
            'count' => count($data),
            'time' => [
                'start' => static::$startTime,
                'finish' => $endTime,
                'duration' => $endTime - static::$startTime,
                'date_start' => date('c', static::$startTime),
                'date_finish' => date('c', $endTime)
            ]
        ];

        //$response['time']['duration'] = $response['time']['finish'] - $response['time']['start'];
        return $response;
    }

    public static function add($arParams, $n, \CRestServer $server)
    {
        static::$startTime = microtime(true);

        if (empty($arParams['LASTNAME']) || empty($arParams['FIRSTNAME'])) {
            throw new RestException('Обязательные поля: LASTNAME, FIRSTNAME', 'INVALID_PARAMS');
        }

        $data = [
            'LASTNAME' => $arParams['LASTNAME'],
            'FIRSTNAME' => $arParams['FIRSTNAME'],
            'MIDDLENAME' => $arParams['MIDDLENAME'] ?? '',
            'DUTY_ID' => (int)($arParams['DUTY_ID'] ?? 0),
            'BIRTHDAY' => $arParams['BIRTHDAY'] ?? null,
        ];

        $result = DoctorsTable::add($data);

        if ($result->isSuccess()) {
            // Триггерим исходящий вебхук
            self::triggerEvent('onAfterOtusDoctorAdd', $result->getId(), $data);

            return self::formatResponse(['ID' => $result->getId()]);
        } else {
            throw new RestException(implode(', ', $result->getErrorMessages()), 'ADD_ERROR');
        }
    }

    public static function get($arParams, $n, \CRestServer $server)
    {
        static::$startTime = microtime(true);

        $id = (int)($arParams['ID'] ?? 0);
        if (!$id) {
            throw new RestException('ID обязателен', 'INVALID_PARAMS');
        }

        $doctor = DoctorsTable::getById($id)->fetch();
        if (!$doctor) {
            throw new RestException('Доктор не найден', 'NOT_FOUND');
        }

        return self::formatResponse($doctor);
    }

    public static function list($arParams, $n, \CRestServer $server)
    {
        static::$startTime = microtime(true);

        $filter = $arParams['filter'] ?? [];
        $select = $arParams['select'] ?? ['*'];
        $order = $arParams['order'] ?? ['ID' => 'ASC'];
        $limit = (int)($arParams['limit'] ?? 50);
        $offset = (int)($arParams['offset'] ?? 0);

        $result = DoctorsTable::getList([
            'filter' => $filter,
            'select' => $select,
            'order' => $order,
            'limit' => $limit,
            'offset' => $offset
        ]);

        $doctors = [];
        while ($doctor = $result->fetch()) {
            $doctors[] = $doctor;
        }

        return self::formatResponse($doctors);
    }

    public static function update($arParams, $n, \CRestServer $server)
    {
        static::$startTime = microtime(true);

        $id = (int)($arParams['ID'] ?? 0);
        if (!$id) {
            throw new RestException('ID обязателен', 'INVALID_PARAMS');
        }

        if (!DoctorsTable::getById($id)->fetch()) {
            throw new RestException('Доктор не найден', 'NOT_FOUND');
        }

        $updateFields = [];
        $allowedFields = ['LASTNAME', 'FIRSTNAME', 'MIDDLENAME', 'DUTY_ID', 'BIRTHDAY'];

        foreach ($allowedFields as $field) {
            if (isset($arParams[$field])) {
                $updateFields[$field] = $arParams[$field];
            }
        }

        if (empty($updateFields)) {
            throw new RestException('Нет данных для обновления', 'NO_DATA');
        }

        $result = DoctorsTable::update($id, $updateFields);

        if ($result->isSuccess()) {
            // Триггерим исходящий вебхук
            self::triggerEvent('onAfterOtusDoctorUpdate', $id, $updateFields);

            return self::formatResponse(['success' => true]);
        } else {
            throw new RestException(implode(', ', $result->getErrorMessages()), 'UPDATE_ERROR');
        }
    }

    public static function delete($arParams, $n, \CRestServer $server)
    {
        static::$startTime = microtime(true);

        $id = (int)($arParams['ID'] ?? 0);
        if (!$id) {
            throw new RestException('ID обязателен', 'INVALID_PARAMS');
        }

        if (!DoctorsTable::getById($id)->fetch()) {
            throw new RestException('Доктор не найден', 'NOT_FOUND');
        }

        $result = DoctorsTable::delete($id);

        if ($result->isSuccess()) {
            // Триггерим исходящий вебхук
            self::triggerEvent('onAfterOtusDoctorDelete', $id, []);

            return self::formatResponse(['success' => true]);
        } else {
            throw new RestException(implode(', ', $result->getErrorMessages()), 'DELETE_ERROR');
        }
    }

    /**
     * Подготовка данных для исходящих вебхуков
     */
    public static function prepareEventData($arParams, $arHandler)
    {
        return [
            'EVENT_NAME' => $arHandler['EVENT_NAME'],
            'EVENT_DATA' => $arParams[0], // ID
            'TIMESTAMP' => time()
        ];
    }

    /**
     * Триггерим исходящие вебхуки
     */
    private static function triggerEvent($eventName, $id, $data)
    {
        $event = new \Bitrix\Main\Event('main', $eventName, [
            'ID' => $id,
            'DATA' => $data,
            'TIMESTAMP' => time()
        ]);
        $event->send();
    }
}
