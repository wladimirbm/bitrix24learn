<?php

//Обработчик изменений в элементе инфоблока
use Bitrix\Main\Loader;

$eventManager = \Bitrix\Main\EventManager::getInstance();

AddEventHandler('crm', 'OnBeforeCrmDealAdd', function (&$arFields) {
    if (empty($arFields['UF_CRM_1770588718'])) {
        return true; // Если авто не указано - пропускаем проверку
    }

    if ($arFields['CATEGORY_ID'] != 1) {
        return true; 
    }

    $carId = $arFields['UF_CRM_1770588718'];

    $finalStages = [
        'C1:WON',      
        'C1:LOSE',     
        'C1:APOLOGY'   
    ];
    if (!CModule::IncludeModule("crm")) {
        echo '!CModule::IncludeModule("crm")';
        return;
    }
    $dbDeals = CCrmDeal::GetListEx(
        [],
        [
            '=UF_CRM_1770588718' => $carId,
            '=CATEGORY_ID' => 1, 
            '!STAGE_ID' => $finalStages 
        ],
        false,
        false,
        ['ID', 'TITLE', 'ASSIGNED_BY_ID', 'STAGE_ID']
    );

    if ($deal = $dbDeals->Fetch()) {
        if (CModule::IncludeModule('im')) {
            CIMNotify::Add([
                'TO_USER_ID' => $arFields['ASSIGNED_BY_ID'],
                'FROM_USER_ID' => 1,
                'MESSAGE' => "⚠️ Нельзя создать сделку: есть активная сделка [#{$deal['ID']}] '{$deal['TITLE']}' (стадия: {$deal['STAGE_ID']})",
                'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM
            ]);
        }

        $arFields['RESULT_MESSAGE'] = "Есть незакрытая сделка #{$deal['ID']} '{$deal['TITLE']}' по этому автомобилю.";
        global $APPLICATION;
        $APPLICATION->ThrowException(
            "Есть незакрытая сделка #{$deal['ID']} '{$deal['TITLE']}' по этому автомобилю. " .
                "Закройте её или выберите другой автомобиль."
        );
        return false;
    }

    return true;
});

AddEventHandler('crm', 'OnBeforeCrmDealUpdate', function(&$arFields) {
    if (empty($arFields['ID'])) {
        return true;
    }
    
    if (empty($arFields['UF_CRM_1770588718'])) {
        return true;
    }
    
    $carId = $arFields['UF_CRM_1770588718'];
    $dealId = $arFields['ID'];
    
    $deal = CCrmDeal::GetByID($dealId, false);
    
    if (!$deal || $deal['CATEGORY_ID'] != 1) {
        return true;
    }
    
    if ($deal['UF_CRM_1770588718'] == $carId) {
        return true;
    }
    
    $finalStages = ['C1:WON', 'C1:LOSE', 'C1:APOLOGY'];
    
    $dbDeals = CCrmDeal::GetListEx(
        [],
        [
            '=UF_CRM_1770588718' => $carId,
            '=CATEGORY_ID' => 1,
            '!ID' => $dealId,
            '!STAGE_ID' => $finalStages
        ],
        false,
        false,
        ['ID', 'TITLE']
    );
    
    if ($dbDeals && $existingDeal = $dbDeals->Fetch()) {

        global $APPLICATION;
        $arFields['RESULT_MESSAGE'] = "Автомобиль уже используется в активной сделке #{$existingDeal['ID']} '{$existingDeal['TITLE']}'";
        $APPLICATION->ThrowException(
            "Автомобиль уже используется в активной сделке #{$existingDeal['ID']} '{$existingDeal['TITLE']}'"
        );
        return false;
    }
    
    return true;
});



AddEventHandler('main', 'OnEndBufferContent', function(&$content) {
    if (strpos($_SERVER['REQUEST_URI'], '/crm/contact/details/') !== false) {
        if (strpos($content, 'local/js/car_detail.js') === false) {
            $script = '<script src="/local/js/car_detail.js"></script>';
            $content = str_replace('</body>', $script . '</body>', $content);
        }
    }
});

// Обработчик для подключения фильтра автомобилей
AddEventHandler('main', 'OnBeforeProlog', function() {
    // Только для страниц сделок
    if (preg_match('#/crm/deal/(edit|details)/#', $_SERVER['REQUEST_URI'])) {
      
        $asset = Bitrix\Main\Page\Asset::getInstance();
        $asset->addJs('/local/js/deal_car_filter.js');
    }
});


/*
AddEventHandler('main', 'OnEndBufferContent', function(&$content) {
    if (strpos($_SERVER['REQUEST_URI'], '/crm/contact/details/') !== false) {
        $script = '
        <script>
        // Простая локализация
        window.CarMessages = {
            CAR_LOADING: "Загрузка истории автомобиля...",
            CAR_ERROR_TITLE: "Ошибка загрузки",
            CAR_ERROR_MESSAGE: "Не удалось загрузить информацию об автомобиле",
            CAR_POPUP_TITLE: "История обслуживания автомобиля",
            BTN_CLOSE: "Закрыть",
            BTN_OPEN_CARD: "Открыть карточку авто",
            BTN_HISTORY: "История"
        };
        </script>
        <script src="/local/js/car_detail.js"></script>';
        
        $content = str_replace('</body>', $script . '</body>', $content);
    }
});



AddEventHandler('main', 'OnProlog', function() {
    // Проверяем, что мы на странице контакта
    $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
    $currentPage = $request->getRequestedPage();
    
    if (strpos($currentPage, '/crm/contact/details/') !== false) {
        // 1. Подключаем необходимые JS библиотеки Битрикс
        CJSCore::Init(['popup', 'ui.buttons', 'ui.notification', 'ui.dialogs.messagebox', 'sidepanel']);
        
        // 2. Подключаем наш кастомный скрипт
        $asset = \Bitrix\Main\Page\Asset::getInstance();
        $asset->addJs('/local/js/car_detail.js');
        
        // 3. Передаем сообщения в JS через inline script
        $messages = [
            'CAR_LOADING' => 'Загрузка истории автомобиля...',
            'CAR_ERROR_TITLE' => 'Ошибка загрузки',
            'CAR_ERROR_MESSAGE' => 'Не удалось загрузить информацию об автомобиле. Попробуйте позже.',
            'CAR_POPUP_TITLE' => 'История обслуживания автомобиля',
            'BTN_CLOSE' => 'Закрыть',
            'BTN_OPEN_CARD' => 'Открыть карточку авто',
            'BTN_HISTORY' => 'История'
        ];
        
        // 4. Выводим скрипт с сообщениями
        echo '<script>BX.message(' . \Bitrix\Main\Web\Json::encode($messages) . ');</script>';
    }
});
*/
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
