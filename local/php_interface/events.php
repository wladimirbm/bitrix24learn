<?php

//Обработчик изменений в элементе инфоблока
use Bitrix\Main\Loader;

$eventManager = \Bitrix\Main\EventManager::getInstance();

$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementAdd', ['\App\Events\IbFieldsHandler', 'onElementAfterUpdate']);
$eventManager->addEventHandler("iblock", "OnAfterIBlockElementUpdate", ['\App\Events\IbFieldsHandler','onElementAfterUpdate']);

$eventManager->addEventHandlerCompatible("crm", "OnAfterCrmDealUpdate", ['\App\Events\CrmFieldsHandler', 'onDealAfterUpdate']);
$eventManager->addEventHandlerCompatible('crm', 'OnBeforeCrmDealDelete', ['\App\Events\CrmFieldsHandler', 'onDealBeforeDelete']);

$eventManager->addEventHandlerCompatible('rest', 'OnRestServiceBuildDescription', ['\Otus\Rest\Events', 'OnRestServiceBuildDescriptionHandler']);

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

       // if (preg_match('/\/crm.*/', GetPagePath())) {
       //    $asset->addString('<script>BX.ready(function () { Dreamsite.crm(' . CUtil::PhpToJSObject($settings) . '); });</script>');
       // }

        //if (preg_match('/\/crm\/company\/details\/.*/', GetPagePath())) {
            //$asset->addString('<link rel="stylesheet" type="text/css" href="/local/js/dreamsite/datatables/datatables.min.css"/><script type="text/javascript" src="/local/js/dreamsite/datatables/datatables.min.js"></script>');
           // $asset->addString('<script>BX.ready(function () { Custom.crmCompany(); });</script>');
        //}

        //На всех страницах
        //$asset->addString('<script>BX.ready(function () { Dreamsite.all(); });</script>');
   }


    // public static function OnBeforePrologHandler()
    // {
    //     CJSCore::Init(array('jquery2'));

    // }

}
 