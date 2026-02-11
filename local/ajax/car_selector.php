<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

// 1. Проверка CSRF токена
if (!check_bitrix_sessid())
{
    echo json_encode([
        'status' => 'error',
        'errors' => [['message' => 'Invalid sessid', 'code' => 'SESSION_ERROR']]
    ]);
    die();
}

// 2. Получаем ID контакта
$contactId = (int)($_REQUEST['contactId'] ?? 0);
if ($contactId <= 0)
{
    echo json_encode([
        'status' => 'error',
        'errors' => [['message' => 'Invalid contact ID', 'code' => 'INPUT_ERROR']]
    ]);
    die();
}

// 3. Подключаем модуль CRM
if (!\Bitrix\Main\Loader::includeModule('crm'))
{
    echo json_encode([
        'status' => 'error',
        'errors' => [['message' => 'CRM module not available', 'code' => 'MODULE_ERROR']]
    ]);
    die();
}

try
{
    // 4. ID смарт-процесса "Гараж"
    $entityTypeId = 1054;
    
    // 5. Получаем фабрику
    $factory = \Bitrix\Crm\Service\Container::getInstance()->getFactory($entityTypeId);
    if (!$factory)
    {
        echo json_encode([
            'status' => 'error',
            'errors' => [['message' => 'Smart process not found', 'code' => 'FACTORY_ERROR']]
        ]);
        die();
    }

    // 6. Формируем фильтр
    // ПРОВЕРЬ ТОЧНОЕ ИМЯ ПОЛЯ! Может быть 'CONTACT_ID' или 'UF_CRM_6_CONTACT'
    $filter = [
        '=CONTACT_ID' => $contactId, // ← Измени на правильное имя поля
    ];

// // Вариант 1 (скорее всего этот):
// $filter = ['=CONTACT_ID' => $contactId];

// // Вариант 2 (если есть пользовательское поле):
// $filter = ['=UF_CRM_6_CONTACT' => $contactId];

// // Вариант 3 (если поле называется иначе):
// $filter = ['=UF_CRM_1770588718_CONTACT' => $contactId]; // Пример

    // 7. Выполняем запрос
    $cars = $factory->getItems([
        'filter' => $filter,
        'select' => ['ID', 'TITLE', 'UF_CRM_6_BRAND', 'UF_CRM_6_MODEL', 'UF_CRM_6_YEAR'],
        'order' => ['ID' => 'DESC'],
        'limit' => 100
    ]);

    // 8. Форматируем ответ
    $result = [
        'status' => 'success',
        'data' => [
            'ENTITIES' => [
                'DYNAMICS_1054' => [
                    'ITEMS' => [],
                    'ITEMS_LAST' => [],
                    'ADDITIONAL_INFO' => [
                        'GROUPS_LIST' => [
                            'crmdynamics_1054' => [
                                'TITLE' => 'Гараж',
                                'TYPE_LIST' => ['DYNAMICS_1054'],
                                'DESC_LESS_MODE' => 'N',
                                'SORT' => 40
                            ]
                        ],
                        'SORT_SELECTED' => 400
                    ]
                ]
            ]
        ]
    ];

    $prefix = 'T' . strtolower(dechex($entityTypeId)) . '_';
    
    foreach ($cars as $car)
    {
        $carId = $car->getId();
        $itemKey = $prefix . $carId;
        
        // Форматируем название
        $brand = $car->get('UF_CRM_6_BRAND');
        $model = $car->get('UF_CRM_6_MODEL');
        $year = $car->get('UF_CRM_6_YEAR');
        
        $nameParts = [];
        if ($brand) $nameParts[] = $brand;
        if ($model) $nameParts[] = $model;
        if ($year) $nameParts[] = "({$year})";
        
        $carName = $nameParts ? implode(' ', $nameParts) : $car->getTitle();

        $result['data']['ENTITIES']['DYNAMICS_1054']['ITEMS'][$itemKey] = [
            'id' => $itemKey,
            'entityType' => 'dynamic_1054',
            'entityId' => $carId,
            'name' => $carName,
            'desc' => '',
            'date' => $car->getCreatedTime()->getTimestamp(),
            'url' => \Bitrix\Crm\Service\Container::getInstance()->getRouter()
                ->getItemDetailUrl($entityTypeId, $carId)->getUri(),
            'urlUseSlider' => 'Y'
        ];
        
        $result['data']['ENTITIES']['DYNAMICS_1054']['ITEMS_LAST'][] = $itemKey;
    }

    echo json_encode($result);

}
catch (\Exception $e)
{
    // Логируем ошибку
    AddMessage2Log('Car selector error: ' . $e->getMessage(), 'crm');
    
    echo json_encode([
        'status' => 'error',
        'errors' => [['message' => 'Server error', 'code' => 'SERVER_ERROR']]
    ]);
}

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php';