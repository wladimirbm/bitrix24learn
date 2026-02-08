<?php

//Обработчик изменений в элементе инфоблока
use Bitrix\Main\Loader;

$eventManager = \Bitrix\Main\EventManager::getInstance();

AddEventHandler('crm', 'OnBeforeCrmDealAdd', function(&$arFields) {
    // Проверяем, что указан автомобиль через ВАШЕ поле
    if (empty($arFields['UF_CRM_1770588718'])) {
        return true; // Если авто не указано - пропускаем проверку
    }
    
    $carId = $arFields['UF_CRM_1770588718']; // ID автомобиля
    
    // Ищем активные сделки по этому авто
    $dbDeals = CCrmDeal::GetList(
        [],
        [
            '=UF_CRM_1770588718' => $carId, // Ваше поле
            '!STAGE_ID' => ['C8:SUCCESS', 'C8:FAIL'] // Исключаем финальные стадии
            // ВАЖНО: замените 'C8:SUCCESS' на реальные ID финальных стадий вашей воронки
        ],
        false,
        false,
        ['ID', 'TITLE', 'ASSIGNED_BY_ID']
    );
    
    if ($deal = $dbDeals->Fetch()) {
        // Отправляем уведомление ответственному
        CIMNotify::Add([
            'TO_USER_ID' => $arFields['ASSIGNED_BY_ID'],
            'FROM_USER_ID' => 1,
            'MESSAGE' => "Нельзя создать сделку: есть незакрытая сделка [#{$deal['ID']}] {$deal['TITLE']} по этому автомобилю",
            'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM
        ]);
        
        // Запрещаем создание
        $GLOBALS['APPLICATION']->ThrowException("Есть незакрытая сделка #{$deal['ID']} по этому автомобилю");
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
 