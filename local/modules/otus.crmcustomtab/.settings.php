<?php

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\UserTable;
use Otus\Entities\Main\UserController;
use Otus\Entities\Main\UserRepository;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();

return [
    'controllers' => [
        'value' => [
            'defaultNamespace' => '\\Otus\\Crmcustomtab\\Controller',
            'namespaces' => [
                '\\Otus\\Crmcustomtab\\Controller\\DoctorActions' => 'doctor',
            ],
        ],
        'readonly' => true,
    ],
    'services' => [
        'value' => [
            'user.controller' => [
                'constructor' => static function () {
                    $userRepository = ServiceLocator::getInstance()->get('user.repository');
                    return new UserController($userRepository);
                }
            ],
            'user.repository' => [
                'constructor' => static function () {
                    return new UserRepository(UserTable::class);
                },
            ],
        ],
    ],
];
