<?php
//Подключение своих CSS и JS к Битрикс24

use Bitrix\Main\EventManager;
EventManager::getInstance()->addEventHandler(
    'main',
    'OnProlog',
    [CustomEvents::class, 'OnProlog']
);

EventManager::getInstance()->AddEventHandler(
    "main",
    "OnBeforeProlog",
    [CustomEvents::class, "OnBeforePrologHandler"]
);

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

        //CJSCore::Init(array('jquery', 'ajax', 'popup'));

        $asset = \Bitrix\Main\Page\Asset::getInstance();

        $settings=[];

       // if (preg_match('/\/crm.*/', GetPagePath())) {
       //    $asset->addString('<script>BX.ready(function () { Dreamsite.crm(' . CUtil::PhpToJSObject($settings) . '); });</script>');
       // }

        if (preg_match('/\/crm\/company\/details\/.*/', GetPagePath())) {
            //$asset->addString('<link rel="stylesheet" type="text/css" href="/local/js/dreamsite/datatables/datatables.min.css"/><script type="text/javascript" src="/local/js/dreamsite/datatables/datatables.min.js"></script>');
           // $asset->addString('<script>BX.ready(function () { Custom.crmCompany(); });</script>');
        }

        //На всех страницах
        //$asset->addString('<script>BX.ready(function () { Dreamsite.all(); });</script>');
    }


    public static function OnBeforePrologHandler()
    {
        CJSCore::Init(array('jquery2'));

    }

}