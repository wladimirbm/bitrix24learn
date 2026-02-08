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

        //return __METHOD__ . '();';
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

        $ownerTypeAbbr = \CCrmOwnerTypeAbbr::ResolveByTypeID(1058);
        error_log('Полученный ownerTypeAbbr для 1058: ' . $ownerTypeAbbr);

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

        if (\Bitrix\Main\Loader::includeModule('im')) {
            // \CIMMessage::Add(array(  
            // 	'FROM_USER_ID' => 1,  
            // 	'TO_USER_ID' => 21, 
            // 	'MESSAGE' => 'Пишу в чат тебе', 
            // ));  
        }

        if (Loader::IncludeModule('im')) {
            $arFields = array(
                "NOTIFY_TITLE" => "Автоматическая закупка", //заголовок
                "MESSAGE" => '✅ Автозакупка: ' . $elementName . '. Остаток восстановлен до 10 единиц.',
                "MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
                "TO_USER_ID" => 1, // 13 ID закупщика
                "FROM_USER_ID" => 1, // От имени какого пользователя (например, система)
                "NOTIFY_TYPE" => IM_NOTIFY_SYSTEM,
                "NOTIFY_MODULE" => "main",
                "NOTIFY_EVENT" => "manage",
            );
            \CIMMessage::Add($arFields);
        }

        return true;
    }
}
