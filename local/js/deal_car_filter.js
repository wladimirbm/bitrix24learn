/**
 * Фильтрация автомобилей по выбранному контакту в сделке
 * Минимальная рабочая версия
 */

(function() {
    'use strict';
    
    const SMART_PROCESS_TYPE_ID = 1054;
    const ENTITY_CODE = 'DYNAMICS_' + SMART_PROCESS_TYPE_ID;
    
    console.log('DealCarFilter: Минимальная версия загружена');
    
    /**
     * ПОЛУЧИТЬ ID КОНТАКТА СЕЙЧАС
     */
    function getContactIdNow() {
        // Способ 1: Ищем значок с data-entity-id ВО ВСЕЙ ФОРМЕ
        const allBadges = document.querySelectorAll('.crm-entity-widget-badge');
        
        for (let badge of allBadges) {
            // Ищем значок рядом с иконкой контакта
            const prev = badge.previousElementSibling;
            if (prev && prev.classList.contains('crm-entity-widget-img-contact')) {
                const id = badge.getAttribute('data-entity-id');
                if (id) return id;
            }
        }
        
        // Способ 2: Ищем ЛЮБОЙ data-entity-id в блоке клиента
        const clientBlock = document.querySelector('[data-cid="CLIENT"]');
        if (clientBlock) {
            const anyId = clientBlock.querySelector('[data-entity-id]');
            if (anyId) return anyId.getAttribute('data-entity-id');
        }
        
        return null;
    }
    
    /**
     * ПЕРЕХВАТИТЬ ЗАПРОСЫ СЕЛЕКТОРА
     */
    function setupSelectorInterception() {
        console.log('DealCarFilter: Настройка перехвата...');
        
        // 1. Перехватываем через ПЕРЕОПРЕДЕЛЕНИЕ BX.ajax.runAction
        if (window.BX && BX.ajax && BX.ajax.runAction) {
            const originalRunAction = BX.ajax.runAction;
            BX.ajax.runAction = function(action, params) {
                // Если это запрос к селектору
                if (action === 'main.ui.selector:getData' || 
                    (params && params.data && params.data.action === 'getData')) {
                    
                    console.log('DealCarFilter: Перехвачен runAction:', action);
                    
                    const contactId = getContactIdNow();
                    console.log('DealCarFilter: Найден контакт:', contactId);
                    
                    if (contactId && params.data && params.data.entityTypes && 
                        params.data.entityTypes[ENTITY_CODE]) {
                        
                        if (!params.data.FILTER) params.data.FILTER = {};
                        params.data.FILTER[ENTITY_CODE] = { '=CONTACT_ID': contactId };
                        
                        console.log('DealCarFilter: Фильтр добавлен в runAction');
                    }
                }
                
                return originalRunAction.apply(this, arguments);
            };
        }
        
        // 2. Перехватываем через XMLHttpRequest (работает точно)
        const originalXHROpen = XMLHttpRequest.prototype.open;
        const originalXHRSend = XMLHttpRequest.prototype.send;
        
        XMLHttpRequest.prototype.open = function(method, url) {
            this._dealCarFilterUrl = url;
            return originalXHROpen.apply(this, arguments);
        };
        
        XMLHttpRequest.prototype.send = function(body) {
            const url = this._dealCarFilterUrl;
            
            // Если это запрос к селектору
            if (url && url.includes('/bitrix/services/main/ajax.php') && 
                url.includes('main.ui.selector')) {
                
                console.log('DealCarFilter: Перехвачен XHR запрос к селектору');
                
                // Парсим body для добавления фильтра
                if (body && typeof body === 'string') {
                    try {
                        const params = new URLSearchParams(body);
                        const action = params.get('action');
                        
                        if (action === 'getData' || action === 'doSearch') {
                            // Получаем контакт ПЕРЕД отправкой
                            const contactId = getContactIdNow();
                            console.log('DealCarFilter: Контакт для XHR:', contactId);
                            
                            if (contactId) {
                                // Добавляем параметр фильтра
                                params.append(`data[FILTER][${ENTITY_CODE}][=CONTACT_ID]`, contactId);
                                body = params.toString();
                                console.log('DealCarFilter: Тело запроса с фильтром:', body);
                            }
                        }
                    } catch(e) {
                        console.error('DealCarFilter: Ошибка парсинга тела:', e);
                    }
                }
            }
            
            return originalXHRSend.call(this, body);
        };
        
        console.log('DealCarFilter: Перехват настроен');
    }
    
    /**
     * ПУБЛИЧНЫЕ МЕТОДЫ ДЛЯ ОТЛАДКИ
     */
    window.DealCarFilter = {
        // Получить текущий контакт
        getContact: function() {
            return getContactIdNow();
        },
        
        // Тест: показать все элементы для отладки
        debug: function() {
            console.log('=== DealCarFilter Debug ===');
            
            // 1. Все значки
            const badges = document.querySelectorAll('.crm-entity-widget-badge');
            console.log('Значки найдены:', badges.length);
            badges.forEach((b, i) => {
                console.log(`Значок ${i}:`, {
                    text: b.textContent,
                    dataId: b.getAttribute('data-entity-id'),
                    prevElement: b.previousElementSibling ? 
                        b.previousElementSibling.className : 'нет'
                });
            });
            
            // 2. Все data-entity-id
            const allIds = document.querySelectorAll('[data-entity-id]');
            console.log('Все data-entity-id:', allIds.length);
            allIds.forEach((el, i) => {
                console.log(`data-entity-id ${i}:`, el.getAttribute('data-entity-id'), el);
            });
            
            // 3. Блок клиента
            const client = document.querySelector('[data-cid="CLIENT"]');
            console.log('Блок клиента:', client ? 'найден' : 'не найден', client);
            
            // 4. Текущий контакт
            console.log('Текущий контакт (через getContactIdNow):', getContactIdNow());
            
            return getContactIdNow();
        },
        
        // Принудительно добавить фильтр в следующий запрос
        testFilter: function(contactId) {
            console.log('DealCarFilter: Тест фильтра для контакта:', contactId || getContactIdNow());
            
            // Создаем тестовый запрос
            const testParams = {
                data: {
                    action: 'getData',
                    entityTypes: {},
                    FILTER: {}
                }
            };
            
            testParams.data.entityTypes[ENTITY_CODE] = { options: {} };
            testParams.data.FILTER[ENTITY_CODE] = { '=CONTACT_ID': contactId || getContactIdNow() || 'TEST' };
            
            console.log('Тестовые параметры:', testParams);
            return testParams;
        },
        
        // Статус
        status: function() {
            return {
                contact: getContactIdNow(),
                typeId: SMART_PROCESS_TYPE_ID,
                entity: ENTITY_CODE,
                url: window.location.pathname
            };
        }
    };
    
    /**
     * ЗАПУСК
     */
    function init() {
        console.log('DealCarFilter: Запуск минимальной версии');
        
        // Даем время странице загрузиться
        setTimeout(() => {
            setupSelectorInterception();
            console.log('DealCarFilter: Готов. Используй DealCarFilter.debug() для проверки');
        }, 1500);
    }
    
    // Запускаем
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Повторный запуск через 3 секунды на всякий случай
    setTimeout(init, 3000);
    
})();