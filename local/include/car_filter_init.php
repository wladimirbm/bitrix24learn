<?php
// Этот файл подключает наш JavaScript на нужных страницах

use Bitrix\Main\Page\Asset;

defined('B_PROLOG_INCLUDED') || die();

/**
 * Подключает скрипт фильтрации автомобилей
 * только на страницах создания/редактирования сделки
 */
function initCarFilter()
{
    $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
    $requestUri = $request->getRequestUri();
    
    // Проверяем, что это страница сделки (детали, создание, редактирование)
    if (preg_match('#/crm/deal/(details|edit|add)/#i', $requestUri)) {
        // Подключаем наш JavaScript
        Asset::getInstance()->addJs('/local/js/car.selector/deal_car_filter.js');
        
        // Можно также добавить CSS для кастомного попапа
        Asset::getInstance()->addCss('/local/css/car.selector/car_filter.css');
        
        \Bitrix\Main\Diag\Debug::writeToFile(
            'CarFilter: Скрипт подключен для ' . $requestUri,
            '',
            '/local/logs/car_filter.log'
        );
    }
}

// Регистрируем обработчик события
AddEventHandler('main', 'OnEpilog', 'initCarFilter');