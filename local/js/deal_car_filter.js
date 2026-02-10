/**
 * Фильтрация автомобилей по выбранному контакту в сделке
 * Автоматически добавляет фильтр =CONTACT_ID в запросы селектора "Автомобиль"
 * ПРОСТАЯ РЕАЛИЗАЦИЯ ЧЕРЕЗ ГЛОБАЛЬНЫЙ ПЕРЕХВАТ ЗАПРОСОВ
 */

(function() {
    'use strict';
    
    const SMART_PROCESS_TYPE_ID = 1054;
    const ENTITY_CODE = 'DYNAMICS_' + SMART_PROCESS_TYPE_ID;
    
    // Будем получать ID контакта ПРЯМО ПЕРЕД каждым запросом
    console.log('DealCarFilter: Простая версия загружена');
    
    /**
     * Получить ID выбранного контакта ПРЯМО СЕЙЧАС из DOM
     */
    function getCurrentContactId() {
        // Ищем значок контакта ВО ВСЕМ ДОКУМЕНТЕ
        const contactBadges = document.querySelectorAll('.crm-entity-widget-badge[data-entity-id]');
        
        for (let badge of contactBadges) {
            // Проверяем, что это именно значок контакта (рядом с иконкой контакта)
            const prevElement = badge.previousElementSibling;
            if (prevElement && prevElement.classList.contains('crm-entity-widget-img-contact')) {
                const contactId = badge.getAttribute('data-entity-id');
                if (contactId) {
                    return contactId;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Перехватываем ВСЕ запросы селекторов и проверяем, наш ли это
     */
    function interceptAllSelectorRequests() {
        console.log('DealCarFilter: Начинаем перехват всех запросов селектора');
        
        // 1. Перехват через нативное переопределение fetch
        const originalFetch = window.fetch;
        window.fetch = function(resource, init) {
            if (typeof resource === 'string' && resource.includes('/bitrix/services/main/ajax.php')) {
                return originalFetch.apply(this, arguments).then(response => {
                    // Клонируем response для чтения
                    const clonedResponse = response.clone();
                    
                    clonedResponse.text().then(text => {
                        try {
                            const data = JSON.parse(text);
                            // Если это ответ от селектора
                            if (data && data.data && data.data.ENTITIES && data.data.ENTITIES[ENTITY_CODE]) {
                                console.log('DealCarFilter: Получен ответ от селектора', data);
                            }
                        } catch(e) {}
                    });
                    
                    return response;
                });
            }
            return originalFetch.apply(this, arguments);
        };
        
        // 2. Перехват через BX.ajax (основной способ Битрикса)
        if (window.BX && BX.ajax && BX.ajax.prepareData) {
            const originalPrepareData = BX.ajax.prepareData;
            BX.ajax.prepareData = function(params) {
                const result = originalPrepareData.apply(this, arguments);
                
                // Проверяем, это ли наш запрос к селектору
                if (result && result.data && 
                    result.data.action && 
                    (result.data.action === 'getData' || result.data.action === 'doSearch') &&
                    result.data.entityTypes && 
                    result.data.entityTypes[ENTITY_CODE]) {
                    
                    console.log('DealCarFilter: Перехвачен запрос селектора через BX.ajax');
                    
                    // ПОЛУЧАЕМ КОНТАКТ ПРЯМО ПЕРЕД ОТПРАВКОЙ
                    const contactId = getCurrentContactId();
                    console.log('DealCarFilter: Текущий контакт (перед запросом):', contactId);
                    
                    if (contactId) {
                        // Добавляем фильтр
                        if (!result.data.FILTER) {
                            result.data.FILTER = {};
                        }
                        result.data.FILTER[ENTITY_CODE] = {
                            '=CONTACT_ID': contactId
                        };
                        console.log('DealCarFilter: Фильтр добавлен:', result.data.FILTER);
                    }
                }
                
                return result;
            };
        }
        
        // 3. Перехват через XMLHttpRequest (на всякий случай)
        const originalXHROpen = XMLHttpRequest.prototype.open;
        XMLHttpRequest.prototype.open = function(method, url) {
            if (url && url.includes('/bitrix/services/main/ajax.php') && 
                url.includes('main.ui.selector')) {
                
                this.addEventListener('load', function() {
                    try {
                        const response = JSON.parse(this.responseText);
                        if (response && response.data && response.data.ENTITIES && 
                            response.data.ENTITIES[ENTITY_CODE]) {
                            console.log('DealCarFilter: Получен ответ через XMLHttpRequest');
                        }
                    } catch(e) {}
                });
            }
            
            return originalXHROpen.apply(this, arguments);
        };
    }
    
    /**
     * Инициализация
     */
    function init() {
        console.log('DealCarFilter: Инициализация простой версии');
        
        // Ждем немного, чтобы DOM точно загрузился
        setTimeout(function() {
            interceptAllSelectorRequests();
            
            // Публичные методы для отладки
            window.DealCarFilter = {
                getCurrentContact: function() {
                    return getCurrentContactId();
                },
                
                testFilter: function() {
                    const contactId = getCurrentContactId();
                    console.log('DealCarFilter: Тест - контакт:', contactId);
                    console.log('DealCarFilter: Тест - найденные значки:', 
                        document.querySelectorAll('.crm-entity-widget-badge[data-entity-id]').length);
                    
                    // Покажем все значки с data-entity-id
                    const allBadges = document.querySelectorAll('[data-entity-id]');
                    allBadges.forEach((badge, i) => {
                        console.log('Значок', i, ':', badge.getAttribute('data-entity-id'), badge);
                    });
                    
                    return contactId;
                },
                
                getStatus: function() {
                    return {
                        initialized: true,
                        currentContact: getCurrentContactId(),
                        smartProcessType: SMART_PROCESS_TYPE_ID,
                        entityCode: ENTITY_CODE,
                        pageUrl: window.location.pathname
                    };
                }
            };
            
            console.log('DealCarFilter: Простая версия инициализирована');
            console.log('DealCarFilter: Используй DealCarFilter.testFilter() для проверки');
            
        }, 1000); // Задержка 1 секунда
    }
    
    // Запускаем при полной загрузке
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Дублирующий запуск через 3 секунды на случай динамической загрузки
    setTimeout(init, 3000);
    
})();