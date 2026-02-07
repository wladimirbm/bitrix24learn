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
        $elements = \Bitrix\Iblock\ElementTable::getList([
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
                'https://www.random.org/integers/?num=1&min=0&max=10&col=1&base=10&format=plain&rnd=new'
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
        
        return __METHOD__ . '();';
    }
    
    private static function createAutoPurchaseRequest($elementId, $elementName)
    {
        // 1. СРАЗУ увеличиваем остаток на 10 единиц
        \Bitrix\Catalog\Model\Product::update($elementId, [
            'QUANTITY' => 10
        ]);
        
        // 2. Создаём элемент смарт-процесса СО СТАТУСОМ "ВЫПОЛНЕНО"
        $factory = \Bitrix\Crm\Service\Container::getInstance()
            ->getFactoryByEntityTypeId(1058); // ID типа смарт-процесса "Заявка на закупку"
            
        if (!$factory) {
            return false;
        }
        
        $item = $factory->createItem([
            'fields' => [
                'TITLE' => '[АВТТО] Закупка: ' . $elementName,
                'STAGE_ID' => 'DT1058_11:SUCCESS', 
                'ASSIGNED_BY_ID' => 13 // ID начальник закупщиков
            ]
        ]);
        
        $requestId = $item->save()->getId();
        
        // 3. Добавляем товар через вкладку «Товары»
        $productRow = \Bitrix\Crm\ProductRow::create([
            'OWNER_ID' => $requestId,
            'OWNER_TYPE' => \CCrmOwnerTypeAbbreviation::ResolveByTypeID(1058),
            'PRODUCT_ID' => $elementId,
            'QUANTITY' => 10,
            'PRICE' => 0,
            'PRICE_EXCLUSIVE' => 0,
            'PRICE_NETTO' => 0,
            'PRICE_BRUTTO' => 0
        ]);
        
        $productRow->save();
        
        // 4. Уведомление закупщику (если подключен модуль im)
        if (Loader::includeModule('im')) {
            \Bitrix\Im::notify([
                'TO_USER_ID' => 1,
                'MESSAGE' => '✅ Автоматическая закупка: ' . $elementName . '. Остаток 10 единиц.',
                'TYPE' => 'SYSTEM'
            ]);
        }
        
        return true;
    }
}
?>