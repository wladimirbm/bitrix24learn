<?php

namespace Local\Components\CarSelector;

use Bitrix\Main\Loader;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Crm\Service\Container;
use Bitrix\Crm\Item;

class CarSelectorController extends Controller
{
    // Константы для твоего смарт-процесса "Гараж"
    const SMART_PROCESS_TYPE_ID = 1054;

    // Настраиваем, какие фильтры CSRF и авторизации применять
    public function configureActions()
    {
        return [
            'getCars' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ],
        ];
    }

    /**
     * Основной метод API. Возвращает автомобили для контакта.
     * Вызывается через: /bitrix/services/main/ajax.php?action=local.car.selector.getCars
     */
    public function getCarsAction(int $contactId)
    {
        // 1. Проверяем обязательные модули
        if (!Loader::includeModule('crm')) {
            return $this->errorResponse('Модуль CRM не доступен');
        }

        // 2. Проверяем входные данные
        if ($contactId <= 0) {
            return $this->errorResponse('Неверный ID контакта');
        }

        try {
            // 3. Получаем фабрику для смарт-процесса "Гараж"
            $factory = Container::getInstance()->getFactory(self::SMART_PROCESS_TYPE_ID);
            
            if (!$factory) {
                return $this->errorResponse('Смарт-процесс "Гараж" не найден');
            }

            // 4. Формируем фильтр: только автомобили этого контакта
            $filter = [
                '=CONTACT_ID' => $contactId, // Или '=UF_CRM_6_CONTACT' в зависимости от точного имени поля
            ];

            // 5. Выбираем только нужные поля для оптимизации
            $select = [
                Item::FIELD_NAME_ID,
                Item::FIELD_NAME_TITLE,
                'UF_CRM_6_BRAND',    // Марка
                'UF_CRM_6_MODEL',    // Модель
                'UF_CRM_6_YEAR',     // Год
                // Добавь другие нужные поля
            ];

            // 6. Выполняем запрос через Factory API
            $cars = $factory->getItems([
                'filter' => $filter,
                'select' => $select,
                'order' => ['ID' => 'DESC'], // Сначала новые
                'limit' => 100, // Лимит на случай большого гаража
            ]);

            // 7. Форматируем ответ как ожидает фронтенд
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

            $prefix = 'T' . strtolower(dechex(self::SMART_PROCESS_TYPE_ID)) . '_';
            
            foreach ($cars as $car) {
                $carId = $car->getId();
                $result['data']['ENTITIES']['DYNAMICS_1054']['ITEMS'][$prefix . $carId] = [
                    'id' => $prefix . $carId,
                    'entityType' => 'dynamic_1054',
                    'entityId' => $carId,
                    'name' => $this->formatCarName($car),
                    'desc' => $this->formatCarDescription($car),
                    'date' => $car->getCreatedTime()->getTimestamp(),
                    'url' => Container::getInstance()->getRouter()->getItemDetailUrl(
                        self::SMART_PROCESS_TYPE_ID,
                        $carId
                    )->getUri(),
                    'urlUseSlider' => 'Y'
                ];
                
                $result['data']['ENTITIES']['DYNAMICS_1054']['ITEMS_LAST'][] = $prefix . $carId;
            }

            return $result;

        } catch (\Exception $e) {
            // Логируем ошибку и возвращаем понятное сообщение
            AddMessage2Log('Ошибка CarSelector: ' . $e->getMessage(), 'crm');
            return $this->errorResponse('Внутренняя ошибка сервера');
        }
    }

    /**
     * Форматирует название автомобиля для отображения
     */
    private function formatCarName(Item $car): string
    {
        $brand = $car->get('UF_CRM_6_BRAND');
        $model = $car->get('UF_CRM_6_MODEL');
        $year = $car->get('UF_CRM_6_YEAR');
        
        $parts = [];
        if ($brand) $parts[] = $brand;
        if ($model) $parts[] = $model;
        if ($year) $parts[] = "({$year})";
        
        return $parts ? implode(' ', $parts) : $car->getTitle();
    }

    /**
     * Форматирует дополнительное описание
     */
    private function formatCarDescription(Item $car): string
    {
        $vin = $car->get('UF_CRM_6_VIN');
        $number = $car->get('UF_CRM_6_NUMBER');
        
        $parts = [];
        if ($vin) $parts[] = "VIN: {$vin}";
        if ($number) $parts[] = "Госномер: {$number}";
        
        return implode(', ', $parts);
    }

    /**
     * Универсальный метод для ошибок
     */
    private function errorResponse(string $message): array
    {
        return [
            'status' => 'error',
            'errors' => [
                ['message' => $message, 'code' => 0]
            ]
        ];
    }
}