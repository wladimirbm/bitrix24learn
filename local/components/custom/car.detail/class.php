<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class CarDetailComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        CModule::IncludeModule('crm');
        CModule::IncludeModule('iblock');

        $this->arResult = $this->getCarData();
        $this->includeComponentTemplate();
    }

    private function getCarData()
    {
        $carId = (int)$_REQUEST['car_id'];
        if (!$carId) {
            return ['ERROR' => 'Автомобиль не найден', 'HAS_ERROR' => true];
        }

        try {
            // 1. Получаем данные об автомобиле
            $factory = \Bitrix\Crm\Service\Container::getInstance()->getFactory(1054);

            if (!$factory) {
                return ['ERROR' => 'Смарт-процесс не найден', 'HAS_ERROR' => true];
            }

            $carItem = $factory->getItem($carId);
            if (!$carItem) {
                return ['ERROR' => 'Автомобиль не найден', 'HAS_ERROR' => true];
            }


            $field = $carItem->get('UF_CRM_6_COLOR'); // Это будет ID значения
            // Чтобы получить текстовое значение:
            //$factory = \Bitrix\Crm\Service\Container::getInstance()->getFactory(1054);
            $fieldDescription = \Bitrix\Crm\Service\Container::getInstance()->getFactory(1054)->getFieldsCollection()->getField('UF_CRM_6_COLOR');
echo $fieldDescription->getType();
            if ($fieldDescription && $fieldDescription->getType() === 'enumeration') {
                $items = $fieldDescription->getItems(); // Массив значений списка
                $colorValue = $items[$field]['VALUE'] ?? ''; // Текстовое значение
            }


            $carData = [
                'ID' => $carItem->getId(),
                'BRAND' => $this->getLinkedEntityName($carItem->get('UF_CRM_6_BRAND'), 1040),
                'MODEL' => $this->getLinkedEntityName($carItem->get('UF_CRM_6_MODEL'), 1046),
                'VIN' => $carItem->get('UF_CRM_6_VIN') ?? '',
                'NUMBER' => $carItem->get('UF_CRM_6_NUMBER') ?? '—',
                'YEAR' => $carItem->get('UF_CRM_6_YEAR') ?? '',
                'MILEAGE' => $carItem->get('UF_CRM_6_MILEAGE') ?? '',
                'COLOR' => $colorValue ?? ($carItem->get('UF_CRM_6_COLOR') ?? ''),
                'OWNER_NAME' => $this->getContactName($carItem->get('UF_CRM_6_CONTACT'))
            ];

            // 2. Получаем активные сделки
            $deals = $this->getActiveDeals($carId);
            $carData['ACTIVE_DEALS_COUNT'] = count($deals);

            // 3. Определяем статус авто
            if (count($deals) > 0) {
                $carData['STATUS_TEXT'] = 'В РАБОТЕ';
                $carData['STATUS_COLOR'] = '#e74c3c';
                $carData['STATUS_DESCRIPTION'] = 'В работе';
            } else {
                $carData['STATUS_TEXT'] = 'СВОБОДЕН';
                $carData['STATUS_COLOR'] = '#27ae60';
                $carData['STATUS_DESCRIPTION'] = 'Свободен';
            }

            return [
                'CAR' => $carData,
                'DEALS' => $deals,
                'HAS_ERROR' => false
            ];
        } catch (Exception $e) {
            return ['ERROR' => 'Ошибка: ' . $e->getMessage(), 'HAS_ERROR' => true];
        }
    }

    private function getLinkedEntityName($entityId, $entityTypeId)
    {
        if (!$entityId) return '—';

        try {
            $factory = \Bitrix\Crm\Service\Container::getInstance()->getFactory($entityTypeId);
            if ($factory) {
                $item = $factory->getItem($entityId);
                if ($item) {
                    return $item->getTitle();
                }
            }
        } catch (Exception $e) {
            // Игнорируем ошибку
        }

        return '—';
    }

    private function getContactName($contactId)
    {
        if (!$contactId) return '—';

        try {
            $contact = CCrmContact::GetByID($contactId);
            if ($contact) {
                $name = trim($contact['NAME'] . ' ' . $contact['LAST_NAME']);
                return $name ?: '—';
            }
        } catch (Exception $e) {
            // Игнорируем ошибку
        }

        return '—';
    }

    private function getActiveDeals($carId)
    {
        $deals = [];

        try {
            // Определяем финальные стадии (завершенные)
            $finalStages = [
                'C1:WON',           // Выполнено
                'C1:LOSE',          // Сделка провалена
                'C1:APOLOGY'        // Анализ причин провала
            ];

            // Получаем ВСЕ сделки, кроме завершенных
            $dbDeals = CCrmDeal::GetListEx(
                ['DATE_CREATE' => 'DESC'], // Сортировка по дате создания (новые сверху)
                [
                    '=UF_CRM_1770588718' => $carId, // Поле связи с авто
                    '=CATEGORY_ID' => 1,           // Только сервисные сделки (воронка 1)
                    '!STAGE_ID' => $finalStages    // Исключаем финальные стадии
                ],
                false, // Группировка
                false, // Навигация
                ['ID', 'TITLE', 'DATE_CREATE', 'STAGE_ID', 'ASSIGNED_BY_ID', 'OPPORTUNITY']
            );

            if ($dbDeals) {
                while ($deal = $dbDeals->Fetch()) {
                    // Получаем имя ответственного
                    $user = CUser::GetByID($deal['ASSIGNED_BY_ID'])->Fetch();
                    $assignedByName = $user ? trim($user['NAME'] . ' ' . $user['LAST_NAME']) : '—';

                    // Получаем товары из сделки
                    $productRows = [];
                    if (class_exists('\CCrmProductRow')) {
                        $productRows = \CCrmProductRow::LoadRows('D', $deal['ID']);
                    }

                    $productsFormatted = [];
                    foreach ($productRows as $product) {
                        $productsFormatted[] = [
                            'NAME' => htmlspecialcharsbx($product['PRODUCT_NAME']),
                            'QUANTITY' => $product['QUANTITY']
                        ];
                    }

                    // Определяем название стадии
                    $stageName = $this->getStageName($deal['STAGE_ID']);

                    $deals[] = [
                        'ID' => $deal['ID'],
                        'TITLE' => $deal['TITLE'] ? htmlspecialcharsbx($deal['TITLE']) : 'Сделка #' . $deal['ID'],
                        'DATE_CREATE' => FormatDate('d.m.Y, H:i', MakeTimeStamp($deal['DATE_CREATE'])),
                        'STAGE_ID' => $deal['STAGE_ID'],
                        'STAGE_NAME' => $stageName,
                        'ASSIGNED_BY_NAME' => htmlspecialcharsbx($assignedByName),
                        'OPPORTUNITY' => $deal['OPPORTUNITY'] ? number_format($deal['OPPORTUNITY'], 0, '', ' ') . ' ₽' : '0 ₽',
                        'PRODUCT_ROWS' => $productsFormatted
                    ];
                }
            }
        } catch (Exception $e) {
            // Логируем ошибку
            AddMessage2Log('Ошибка получения сделок: ' . $e->getMessage());
        }

        return $deals;
    }

    private function getStageName($stageId)
    {
        $stageNames = [
            // Активные стадии (текущие)
            'C1:NEW' => 'Приёмка',
            'C1:PREPARATION' => 'Диагностика',
            'C1:PREPAYMENT_INVOICE' => 'Ожидание запчастей',
            'C1:EXECUTING' => 'Ремонт',
            'C1:FINAL_INVOICE' => 'Проверка',

            // Финальные стадии (не должны показываться)
            'C1:WON' => 'Выполнено',
            'C1:LOSE' => 'Сделка провалена',
            'C1:APOLOGY' => 'Анализ причин провала'
        ];

        return $stageNames[$stageId] ?? $stageId;
    }
}
