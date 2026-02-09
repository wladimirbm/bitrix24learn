<?php

namespace Otus\Smarts;

use Bitrix\Main\Loader;

class Agents
{
    public static function updatePartsStock()
    {
        Loader::includeModule('catalog');
        Loader::includeModule('iblock');
        Loader::includeModule('crm');

        $iblockId = 14;     // ID инфоблока каталога
        $sectionId = 16;    // ID раздела "Запчасти"

        // $elements = \CIBlockElement::getList([
        //     'filter' => [
        //         'IBLOCK_ID' => $iblockId,
        //         'SECTION_ID' => $sectionId,
        //         'IBLOCK_SECTION_ID' => $sectionId,
        //         'IBLOCK_TYPE' => 'CRM_PRODUCT_CATALOG',
        //         //'INCLUDE_SUBSECTIONS' => 'Y'
        //     ],
        //     'select' => ['ID', 'NAME']
        // ]);

        $elements = \Bitrix\Iblock\ElementTable::getList([
            'filter' => [
                'IBLOCK_ID' => $iblockId,
                'IBLOCK_SECTION_ID' => $sectionId,
                //'INCLUDE_SUBSECTIONS' => 'Y'
            ],
            'select' => ['ID', 'NAME']
        ]);


        // while ($element = $elements->fetch()) {
        //     \App\Debug\Mylog::addLog($element, 'Товары', '', __FILE__, __LINE__);
        // }
        // die();

        while ($element = $elements->fetch()) {
            $newQuantity = file_get_contents(
                'https://www.random.org/integers/?num=1&min=0&max=10&col=1&base=10&format=plain&rnd=new'
            );
            $newQuantity = (int)trim($newQuantity);

            \Bitrix\Catalog\Model\Product::update($element['ID'], [
                'QUANTITY' => $newQuantity
            ]);

            if ($newQuantity === 0) {
                self::createAutoPurchaseRequest($element['ID'], $element['NAME']);
            }
        }

        return __METHOD__ . '();';
    }

    private static function createAutoPurchaseRequest($elementId, $elementName)
    {
        \Bitrix\Catalog\Model\Product::update($elementId, ['QUANTITY' => 10]);

        $factory_id = '1058';
        $factory = \Bitrix\Crm\Service\Container::getInstance()->getFactory($factory_id);

        if (!$factory) {
            \App\Debug\Mylog::addLog($factory, 'Фабрика для 1058 не найдена', '', __FILE__, __LINE__);
            return false;
        }

        $item = $factory->createItem();

        $item->setFromCompatibleData([
            'TITLE' => '[АВТО] Закупка: ' . $elementName,
            'STAGE_ID' => 'DT1058_11:SUCCESS', // Убедитесь, что статус существует
            'ASSIGNED_BY_ID' => 13
        ]);

        $operation = $factory->getAddOperation($item);
        $result = $operation->launch();

        if (!$result->isSuccess()) {
            \App\Debug\Mylog::addLog(print_r($result->getErrorMessages(), true), 'Ошибка создания заявки через Operation', '', __FILE__, __LINE__);
            return false;
        }

        $requestId = $item->getId();
        \App\Debug\Mylog::addLog($requestId, 'id заявки', '', __FILE__, __LINE__);

        $ownerTypeAbbr = \CCrmOwnerTypeAbbr::ResolveByTypeID(1058);
        \App\Debug\Mylog::addLog($ownerTypeAbbr, 'Полученный ownerTypeAbbr для 1058', '', __FILE__, __LINE__);

        $rows = array();
        $rows[] = array(
            'PRODUCT_ID' => $elementId, // ID товара из каталога
            'PRODUCT_NAME' => $elementName, // Название как резерв
            'QUANTITY' => 10,
            'MEASURE_CODE' => 796 // Штуки (код из справочника)
        );

        \CCrmProductRow::SaveRows($ownerTypeAbbr, $requestId, $rows);

        $dbRes = \CCrmProductRow::GetList(
            array(),
            array(
                'OWNER_ID' => $requestId,
                'OWNER_TYPE' => $ownerTypeAbbr
            )
        );
        $checkRows = array();
        while ($row = $dbRes->Fetch()) {
            $checkRows[] = $row;
        }

        if (empty($checkRows)) {
            error_log('Товары не добавились к заявке ID ' . $requestId);
            \App\Debug\Mylog::addLog($requestId, 'Товары не добавились к заявке ID', '', __FILE__, __LINE__);

            return false;
        }

        // if (\Bitrix\Main\Loader::includeModule('im')) {
        // \CIMMessage::Add(array(  
        // 	'FROM_USER_ID' => 1,  
        // 	'TO_USER_ID' => 21, 
        // 	'MESSAGE' => 'Пишу в чат тебе', 
        // ));  
        // }

        if (Loader::IncludeModule('im')) {
            $arFields = array(
                "NOTIFY_TITLE" => "Автоматическая закупка", //заголовок
                "MESSAGE" => '✅ Автозакупка: ' . $elementName . '. Остаток восстановлен до 10 единиц.',
                "MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
                "TO_USER_ID" => 13, // 13 ID закупщика
                "FROM_USER_ID" => 1, // От имени какого пользователя (например, система)
                "NOTIFY_TYPE" => IM_NOTIFY_SYSTEM,
                "NOTIFY_MODULE" => "main",
                "NOTIFY_EVENT" => "manage",
            );
            \CIMMessage::Add($arFields);
        }

        return true;
    }

    public static function bpcodephpDeal()
    {
        $document = [];
        
        $rootActivity = $this->GetRootActivity(); // Получаем корневое действие
        try {
            // 4. Получаем ID сделки
            $dealId = $document['ID'];

            // 5. Получаем товары из сделки
            // Для СДЕЛОК (не смарт-процесса) entityTypeId = \CCrmOwnerType::Deal
            $ownerTypeAbbr = \CCrmOwnerTypeAbbr::ResolveByTypeID(\CCrmOwnerType::Deal);
            $rows = \CCrmProductRow::LoadRows($ownerTypeAbbr, $dealId);

            if (empty($rows)) {
                $this->WriteToTrackingService("В сделке #{$dealId} нет товаров для списания");
                return true; // Выходим, но не прерываем процесс
            }

            // 6. Для каждого товара УМЕНЬШАЕМ остатки
            $consumedProducts = [];

            foreach ($rows as $row) {
                $productId = (int)$row['PRODUCT_ID'];
                $quantity = (float)$row['QUANTITY'];

                if ($productId <= 0 || $quantity <= 0) {
                    continue;
                }

                // Получаем текущее количество товара
                $dbProduct = \Bitrix\Catalog\Model\Product::getList([
                    'filter' => ['ID' => $productId],
                    'select' => ['ID', 'QUANTITY', 'NAME']
                ]);

                if ($product = $dbProduct->fetch()) {
                    $currentQty = (float)$product['QUANTITY'];

                    // Проверяем, достаточно ли товара на складе
                    if ($currentQty < $quantity) {
                        // Недостаточно товара - логируем ошибку
                        $productName = $product['NAME'] ?? $row['PRODUCT_NAME'];
                        $this->WriteToTrackingService("⚠️ Недостаточно товара: {$productName} (ID:{$productId}). На складе: {$currentQty}, требуется: {$quantity}");

                        // Можно отправить уведомление
                        sendStockAlert($dealId, $productId, $productName, $currentQty, $quantity);
                        continue;
                    }

                    // ВЫЧИТАЕМ количество
                    $newQty = $currentQty - $quantity;

                    // Обновляем количество
                    $updateResult = \Bitrix\Catalog\Model\Product::update($productId, [
                        'QUANTITY' => $newQty
                    ]);

                    if ($updateResult->isSuccess()) {
                        $consumedProducts[] = [
                            'id' => $productId,
                            'name' => $product['NAME'] ?? $row['PRODUCT_NAME'],
                            'used' => $quantity,
                            'was' => $currentQty,
                            'now' => $newQty
                        ];

                        $this->WriteToTrackingService("📦 Списание: {$product['NAME']} (ID:{$productId}): {$currentQty} - {$quantity} = {$newQty}");

                        // 7. Проверяем, не опустился ли остаток до 0
                        if ($newQty == 0) {
                            $this->WriteToTrackingService("⚠️ Товар {$product['NAME']} (ID:{$productId}) закончился!");
                            // Можно автоматически создать заявку на закупку
                            createAutoPurchaseForZeroStock($productId, $product['NAME']);
                        }
                    } else {
                        $this->WriteToTrackingService("❌ Ошибка списания товара {$productId}: " . implode(', ', $updateResult->getErrorMessages()));
                    }
                } else {
                    $this->WriteToTrackingService("❌ Товар с ID {$productId} не найден в каталоге");
                }
            }

            // 8. Логируем итог
            if (!empty($consumedProducts)) {
                $totalUsed = array_sum(array_column($consumedProducts, 'used'));
                $this->WriteToTrackingService("✅ Сделка #{$dealId}: списано " . count($consumedProducts) . " товаров, всего: {$totalUsed} ед.");

                // 9. Обновляем поле в сделке (опционально)
                updateDealConsumptionInfo($dealId, $consumedProducts);
            }

            return true;
        } catch (Exception $e) {
            $this->WriteToTrackingService("❌ Ошибка в роботе списания: " . $e->getMessage());
            return false;
        }
    }

    public static function bpcodephpD1058()
    {

        $document = [];
        try {
            // 3. Получаем товары из текущей заявки
            $requestId = $document['ID'];
            $ownerTypeAbbr = \CCrmOwnerTypeAbbr::ResolveByTypeID(1058);
            $rows = \CCrmProductRow::LoadRows($ownerTypeAbbr, $requestId);

            if (empty($rows)) {
                $this->WriteToTrackingService("В заявке #{$requestId} нет товаров");
                return true; // Выходим, но не прерываем процесс
            }

            // 4. Для каждого товара обновляем остатки
            $updatedProducts = [];

            foreach ($rows as $row) {
                $productId = (int)$row['PRODUCT_ID'];
                $quantity = (float)$row['QUANTITY'];

                if ($productId <= 0 || $quantity <= 0) {
                    continue;
                }

                // Получаем текущее количество товара
                $dbProduct = \Bitrix\Catalog\Model\Product::getList([
                    'filter' => ['ID' => $productId],
                    'select' => ['ID', 'QUANTITY']
                ]);

                if ($product = $dbProduct->fetch()) {
                    $currentQty = (float)$product['QUANTITY'];
                    $newQty = $currentQty + $quantity;

                    // Обновляем количество
                    $updateResult = \Bitrix\Catalog\Model\Product::update($productId, [
                        'QUANTITY' => $newQty
                    ]);

                    if ($updateResult->isSuccess()) {
                        $updatedProducts[] = [
                            'id' => $productId,
                            'name' => $row['PRODUCT_NAME'],
                            'added' => $quantity,
                            'new_total' => $newQty
                        ];

                        $this->WriteToTrackingService("Товар {$productId}: {$currentQty} + {$quantity} = {$newQty}");
                    } else {
                        $this->WriteToTrackingService("Ошибка обновления товара {$productId}: " . implode(', ', $updateResult->getErrorMessages()));
                    }
                } else {
                    $this->WriteToTrackingService("Товар с ID {$productId} не найден в каталоге");
                }
            }

            // 5. Логируем результат
            if (!empty($updatedProducts)) {
                $this->WriteToTrackingService("Заявка #{$requestId}: обновлено " . count($updatedProducts) . " товаров");
            }

            return true;
        } catch (Exception $e) {
            $this->WriteToTrackingService("Ошибка в роботе закупки: " . $e->getMessage());
            return false;
        }
    }
}
