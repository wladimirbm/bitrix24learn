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

        // 1. Получаем ТОЛЬКО товары из раздела «Запчасти»
        $elements = \CIBlockElement::getList([
            'filter' => [
                'IBLOCK_ID' => $iblockId,
                'SECTION_ID' => $sectionId,
                'INCLUDE_SUBSECTIONS' => 'Y'
            ],
            'select' => ['ID', 'NAME']
        ]);

        while ($element = $elements->fetch()) {
            // 2. Запрос к random.org за случайным остатком (0-10)
            $newQuantity = file_get_contents(
                'https://www.random.org/integers/?num=1&min=0&max=5&col=1&base=10&format=plain&rnd=new'
            );
            $newQuantity = (int)trim($newQuantity);

            // 3. Обновляем системное поле остатка
            \Bitrix\Catalog\Model\Product::update($element['ID'], [
                'QUANTITY' => $newQuantity
            ]);

            // 4. Если остаток = 0 → создаём автоматическую заявку на закупку
            if ($newQuantity === 0) {
                self::createAutoPurchaseRequest($element['ID'], $element['NAME']);
            }
        }

        //return __METHOD__ . '();';
    }

    private static function createAutoPurchaseRequest($elementId, $elementName)
    {
        // 1. Увеличиваем остаток
        \Bitrix\Catalog\Model\Product::update($elementId, ['QUANTITY' => 10]);

        $factory_id = '1058';
        $factory = \Bitrix\Crm\Service\Container::getInstance()->getFactory($factory_id);

        //\App\Debug\Mylog::addLog($factory, 'Factory', '', __FILE__, __LINE__);
        if (!$factory) {
            \App\Debug\Mylog::addLog($factory, 'Фабрика для 1058 не найдена', '', __FILE__, __LINE__);
            //error_log('Фабрика для DYNAMIC_1058/1058 не найдена');
            return false;
        }

        // 3. Создаём новый элемент по правилам фабрики
        $item = $factory->createItem();

        // 4. Заполняем данные через setFromCompatibleData (рекомендованный способ)
        $item->setFromCompatibleData([
            'TITLE' => '[АВТО] Закупка: ' . $elementName,
            'STAGE_ID' => 'DT1058_11:SUCCESS', // Убедитесь, что статус существует
            'ASSIGNED_BY_ID' => 13
        ]);

        // 5. СОЗДАЁМ И ЗАПУСКАЕМ ОПЕРАЦИЮ ДОБАВЛЕНИЯ
        $operation = $factory->getAddOperation($item);
        $result = $operation->launch();

        if (!$result->isSuccess()) {
            //error_log('Ошибка создания заявки через Operation: ' . print_r($result->getErrorMessages(), true));
            \App\Debug\Mylog::addLog(print_r($result->getErrorMessages(), true), 'Ошибка создания заявки через Operation', '', __FILE__, __LINE__);
            return false;
        }

        $requestId = $item->getId();
        \App\Debug\Mylog::addLog($requestId, 'id заявки', '', __FILE__, __LINE__);

        // 4. ПОЛУЧАЕМ entityTypeAbbr через системный метод
        $ownerTypeAbbr = \CCrmOwnerTypeAbbr::ResolveByTypeID(1058);
        //error_log('Полученный ownerTypeAbbr для 1058: ' . $ownerTypeAbbr); // Для отладки
        \App\Debug\Mylog::addLog($ownerTypeAbbr, 'Полученный ownerTypeAbbr для 1058', '', __FILE__, __LINE__);

        // 6. Получаем entityTypeAbbr
        $ownerTypeAbbr = \CCrmOwnerTypeAbbr::ResolveByTypeID(1058);
        error_log('Полученный ownerTypeAbbr для 1058: ' . $ownerTypeAbbr);

        // 7. Подготавливаем данные товара
        $rows = array();
        $rows[] = array(
            'PRODUCT_ID' => $elementId, // ID товара из каталога
            'PRODUCT_NAME' => $elementName, // Название как резерв
            'QUANTITY' => 10,
            'PRICE' => 0, // Можно взять из каталога, если важно
            'MEASURE_CODE' => 796 // Штуки (код из справочника)
        );

        // 8. Добавляем товар к заявке через SaveRows
        \CCrmProductRow::SaveRows($ownerTypeAbbr, $requestId, $rows);


        // 6. ПРОВЕРКА: получаем товары через GetList
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

        // 6. Уведомление
        if (Loader::includeModule('im')) {
            \Bitrix\Im::notify([
                'TO_USER_ID' => 1, //13
                'MESSAGE' => '✅ Автозакупка: ' . $elementName . '. Остаток 10 ед.',
                'TYPE' => 'SYSTEM'
            ]);
        }

        return true;
    }
}
