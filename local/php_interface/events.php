<?php

//Обработчик изменений в элементе инфоблока
use Bitrix\Main\Loader;

$eventManager = \Bitrix\Main\EventManager::getInstance();

// /local/php_interface/init.php
AddEventHandler('crm', 'OnBeforeCrmDealAdd', function (&$arFields) {
    // Проверяем, что указан автомобиль
    if (empty($arFields['UF_CRM_1770588718'])) {
        return true; // Если авто не указано - пропускаем проверку
    }

    // Проверяем, что сделка из нужной воронки (Сервисное обслуживание)
    if ($arFields['CATEGORY_ID'] != 1) {
        return true; // Проверяем только для сервисных сделок
    }

    $carId = $arFields['UF_CRM_1770588718'];

    // Финальные стадии вашей воронки
    $finalStages = [
        'C1:WON',      // Выполнено
        'C1:LOSE',     // Сделка провалена
        'C1:APOLOGY'   // Анализ причин провала
    ];
    if (!CModule::IncludeModule("crm")) {
        echo '!CModule::IncludeModule("crm")';
        return;
    }
    // Ищем НЕзакрытые сделки по этому авто
    $dbDeals = CCrmDeal::GetListEx(
        [],
        [
            '=UF_CRM_1770588718' => $carId,
            '=CATEGORY_ID' => 1, // Только сделки сервисного обслуживания
            '!STAGE_ID' => $finalStages // Исключаем финальные стадии
        ],
        false,
        false,
        ['ID', 'TITLE', 'ASSIGNED_BY_ID', 'STAGE_ID']
    );

    if ($deal = $dbDeals->Fetch()) {
        // Отправляем уведомление ответственному
        if (CModule::IncludeModule('im')) {
            CIMNotify::Add([
                'TO_USER_ID' => $arFields['ASSIGNED_BY_ID'],
                'FROM_USER_ID' => 1,
                'MESSAGE' => "⚠️ Нельзя создать сделку: есть активная сделка [#{$deal['ID']}] '{$deal['TITLE']}' (стадия: {$deal['STAGE_ID']})",
                'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM
            ]);
        }

        $GLOBALS['APPLICATION']->ThrowException(
            "Есть незакрытая сделка #{$deal['ID']} '{$deal['TITLE']}' по этому автомобилю. " .
                "Закройте её или выберите другой автомобиль."
        );
        return false;
    }

    return true;
});

AddEventHandler('crm', 'OnBeforeCrmDealUpdate', function ($id, &$arFields) {
    // Проверяем, если меняется автомобиль
    if (empty($arFields['UF_CRM_1770588718'])) {
        return true;
    }

    $carId = $arFields['UF_CRM_1770588718'];
    $deal = CCrmDeal::GetByID($id, false);

    if (!$deal || $deal['CATEGORY_ID'] != 1) {
        return true;
    }

    // Если автомобиль не меняется - пропускаем
    if ($deal['UF_CRM_1770588718'] == $carId) {
        return true;
    }

    // Проверяем, не занят ли новый автомобиль
    $finalStages = ['C1:WON', 'C1:LOSE', 'C1:APOLOGY'];

    $dbDeals = CCrmDeal::GetListEx(
        [],
        [
            '=UF_CRM_1770588718' => $carId,
            '=CATEGORY_ID' => 1,
            '!ID' => $id, // Исключаем текущую сделку
            '!STAGE_ID' => $finalStages
        ],
        false,
        false,
        ['ID', 'TITLE']
    );

    if ($existingDeal = $dbDeals->Fetch()) {
        $GLOBALS['APPLICATION']->ThrowException(
            "Автомобиль уже используется в активной сделке #{$existingDeal['ID']}"
        );
        return false;
    }

    return true;
});


/*
$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementAdd', ['\App\Events\IbFieldsHandler', 'onElementAfterUpdate']);
$eventManager->addEventHandler("iblock", "OnAfterIBlockElementUpdate", ['\App\Events\IbFieldsHandler','onElementAfterUpdate']);

$eventManager->addEventHandlerCompatible("crm", "OnAfterCrmDealUpdate", ['\App\Events\CrmFieldsHandler', 'onDealAfterUpdate']);
$eventManager->addEventHandlerCompatible('crm', 'OnBeforeCrmDealDelete', ['\App\Events\CrmFieldsHandler', 'onDealBeforeDelete']);

$eventManager->addEventHandlerCompatible('rest', 'OnRestServiceBuildDescription', ['Otus\Rest\Events', 'OnRestServiceBuildDescriptionHandler']);

// подключение кастомного типа пользовательского поля
$eventManager->AddEventHandler(
    'iblock',
    'OnIBlockPropertyBuildList',
    ['UserTypes\BookingProcedureType', 'GetUserTypeDescription']
);

CJSCore::RegisterExt('otus_booking', [
    'js' => '/local/js/otus/booking/popup.js',
    'rel' => ['popup', 'ui.notification', 'ui.dialogs.messagebox']
]);

CJSCore::Init(['otus_booking']);

//Подключение своих CSS и JS к Битрикс24

\Bitrix\Main\UI\Extension::load('otus.confirm_workday');

// CJSCore::RegisterExt('otus_confirm_workday', [
//     'js' => '/local/js/otus/confirm_workday/main.js',
//     'rel' => ['ui.popup', 'main.core']
// ]);
// CJSCore::Init(['otus_confirm_workday']);

use Bitrix\Main\EventManager;
EventManager::getInstance()->addEventHandler(
    'main',
    'OnProlog',
    [CustomEvents::class, 'OnProlog']
);

// EventManager::getInstance()->AddEventHandler(
//     "main",
//     "OnBeforeProlog",
//     [CustomEvents::class, "OnBeforePrologHandler"]
// );

class CustomEvents
{
    public static function OnProlog()
    {
        global $USER;
        $arJsConfig = array(
            'custom_start' => array(
                'js' => '/local/addition/main.js',
                'css' => '/local/addition/main.css',
                'rel' => array()
            )
        );
        foreach ($arJsConfig as $ext => $arExt) {
            \CJSCore::RegisterExt($ext, $arExt);
        }
        CUtil::InitJSCore(array('custom_start'));

        CJSCore::Init(array('jquery', 'ajax', 'popup'));

       // $asset = \Bitrix\Main\Page\Asset::getInstance();

       // $settings=[];
*/
       // if (preg_match('/\/crm.*/', GetPagePath())) {

       //    $asset->addString('<script>BX.ready(function () { Dreamsite.crm(' . CUtil::PhpToJSObject($settings) . '); });</script>');
       // }

        //if (preg_match('/\/crm\/company\/details\/.*/', GetPagePath())) {
            //$asset->addString('<link rel="stylesheet" type="text/css" href="/local/js/dreamsite/datatables/datatables.min.css"/><script type="text/javascript" src="/local/js/dreamsite/datatables/datatables.min.js"></script>');
           // $asset->addString('<script>BX.ready(function () { Custom.crmCompany(); });</script>');
        //}

        //На всех страницах
        //$asset->addString('<script>BX.ready(function () { Dreamsite.all(); });</script>');
/*   }


    // public static function OnBeforePrologHandler()
    // {
    //     CJSCore::Init(array('jquery2'));

    // }

} */
