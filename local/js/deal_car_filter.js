/**
 * Фильтрация автомобилей по выбранному контакту в сделке
 * ПРОСТАЯ РАБОЧАЯ ВЕРСИЯ
 */

(function() {
    'use strict';
    
    const SMART_PROCESS_TYPE_ID = 1054;
    const ENTITY_CODE = 'DYNAMICS_' + SMART_PROCESS_TYPE_ID;
    
    let currentContactId = null;
    
    console.log('DealCarFilter: Простая версия загружена');
    
    /**
     * 1. НАЙТИ ID КОНТАКТА ИЗ data-cid
     */
    function findContactId() {
        // Ищем секцию контакта: data-cid="CONTACT_7_client_editor_SECTION"
        const contactSection = document.querySelector('[data-cid^="CONTACT_"]');
        
        if (contactSection) {
            const dataCid = contactSection.getAttribute('data-cid');
            const match = dataCid.match(/CONTACT_(\d+)_/);
            
            if (match && match[1]) {
                return match[1]; // Возвращаем ID (например, "7")
            }
        }
        
        return null;
    }
    
    /**
     * 2. ОТСЛЕЖИВАТЬ ПОЯВЛЕНИЕ СЕКЦИИ КОНТАКТА
     */
    function watchForContactSelection() {
        // Наблюдаем за всем документом
        const observer = new MutationObserver(function(mutations) {
            for (let mutation of mutations) {
                // Проверяем добавленные узлы
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    for (let node of mutation.addedNodes) {
                        if (node.nodeType === 1) { // Element node
                            // Проверяем сам элемент или его детей
                            const contactSection = node.querySelector ? 
                                node.querySelector('[data-cid^="CONTACT_"]') : null;
                            
                            if ((node.matches && node.matches('[data-cid^="CONTACT_"]')) || 
                                contactSection) {
                                
                                // Даем время для полной загрузки
                                setTimeout(() => {
                                    const newContactId = findContactId();
                                    
                                    if (newContactId && newContactId !== currentContactId) {
                                        currentContactId = newContactId;
                                        console.log('DealCarFilter: Контакт выбран! ID:', currentContactId);
                                        
                                        // СБРАСЫВАЕМ КЕШ СЕЛЕКТОРА АВТОМОБИЛЕЙ
                                        resetCarSelector();
                                    }
                                }, 100);
                                break;
                            }
                        }
                    }
                }
                
                // Проверяем изменения атрибутов
                if (mutation.type === 'attributes' && 
                    mutation.attributeName === 'data-cid' &&
                    mutation.target.getAttribute('data-cid').startsWith('CONTACT_')) {
                    
                    setTimeout(() => {
                        const newContactId = findContactId();
                        if (newContactId && newContactId !== currentContactId) {
                            currentContactId = newContactId;
                            console.log('DealCarFilter: Контакт изменен! ID:', currentContactId);
                            resetCarSelector();
                        }
                    }, 100);
                }
            }
        });
        
        // Начинаем наблюдение
        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['data-cid']
        });
        
        console.log('DealCarFilter: Наблюдение за выбором контакта включено');
    }
    
    /**
     * 3. СБРОСИТЬ СЕЛЕКТОР АВТОМОБИЛЕЙ
     */
    function resetCarSelector() {
        console.log('DealCarFilter: Сброс селектора автомобилей');
        
        // Находим поле "Автомобиль"
        const carField = document.getElementById('UF_CRM_1770588718');
        if (!carField) {
            console.warn('DealCarFilter: Поле автомобиля не найдено');
            return;
        }
        
        // Очищаем значение
        carField.value = '';
        
        // Очищаем UI плитки
        const tileContainer = document.querySelector('.ui-tile-selector-selector-wrap');
        if (tileContainer) {
            const tiles = tileContainer.querySelectorAll('[data-role="tile-item"]');
            tiles.forEach(tile => tile.remove());
        }
        
        // Очищаем кеш в глобальном хранилище (если есть)
        if (window.BX && BX.Main && BX.Main.SelectorManager && BX.Main.SelectorManager.DataStore) {
            Object.keys(BX.Main.SelectorManager.DataStore).forEach(key => {
                if (key.includes('DYNAMICS_1054') || key.includes('UF_CRM_1770588718')) {
                    delete BX.Main.SelectorManager.DataStore[key];
                }
            });
        }
    }
    
    /**
     * 4. ПЕРЕХВАТИТЬ ЗАПРОСЫ СЕЛЕКТОРА
     */
    function interceptSelectorRequests() {
        // Перехватываем через XMLHttpRequest
        const originalXHROpen = XMLHttpRequest.prototype.open;
        const originalXHRSend = XMLHttpRequest.prototype.send;
        
        XMLHttpRequest.prototype.open = function(method, url) {
            this._dealCarUrl = url;
            return originalXHROpen.apply(this, arguments);
        };
        
        XMLHttpRequest.prototype.send = function(body) {
            const url = this._dealCarUrl;
            
            // Если это запрос к селектору автомобилей
            if (url && url.includes('/bitrix/services/main/ajax.php') && 
                url.includes('main.ui.selector')) {
                
                // Парсим тело запроса
                if (body && typeof body === 'string') {
                    const params = new URLSearchParams(body);
                    const action = params.get('action');
                    
                    // Проверяем, что это запрос для DYNAMICS_1054
                    const isCarRequest = params.has('data[entityTypes][DYNAMICS_1054][options][typeId]') ||
                                        params.get('data[options][enableCrmDynamics][1054]') === 'Y';
                    
                    if ((action === 'getData' || action === 'doSearch') && isCarRequest) {
                        
                        console.log('DealCarFilter: Запрос селектора автомобилей', action);
                        console.log('DealCarFilter: Текущий контакт:', currentContactId);
                        
                        // ЕСЛИ КОНТАКТ ВЫБРАН - ДОБАВЛЯЕМ ФИЛЬТР
                        if (currentContactId) {
                            console.log('DealCarFilter: Добавляем фильтр для контакта', currentContactId);
                            
                            // Добавляем параметр фильтра
                            params.append(`data[FILTER][${ENTITY_CODE}][=CONTACT_ID]`, currentContactId);
                            
                            // Обновляем тело запроса
                            body = params.toString();
                        }
                    }
                }
            }
            
            return originalXHRSend.call(this, body);
        };
        
        console.log('DealCarFilter: Перехват запросов настроен');
    }
    
    /**
     * 5. ПУБЛИЧНЫЕ МЕТОДЫ ДЛЯ ОТЛАДКИ
     */
    window.DealCarFilter = {
        // Получить текущий контакт
        getContact: function() {
            return currentContactId;
        },
        
        // Найти контакт сейчас
        findContactNow: function() {
            return findContactId();
        },
        
        // Принудительно сбросить селектор
        reset: function() {
            resetCarSelector();
            console.log('DealCarFilter: Селектор сброшен вручную');
        },
        
        // Отладка
        debug: function() {
            console.log('=== DealCarFilter Debug ===');
            console.log('Текущий контакт (в памяти):', currentContactId);
            console.log('Найденный контакт (в DOM):', findContactId());
            
            // Показать все data-cid
            const allDataCids = document.querySelectorAll('[data-cid]');
            console.log('Все data-cid на странице:', allDataCids.length);
            allDataCids.forEach(el => {
                console.log('  ', el.getAttribute('data-cid'), el);
            });
            
            return {
                memory: currentContactId,
                dom: findContactId()
            };
        }
    };
    
    /**
     * ИНИЦИАЛИЗАЦИЯ
     */
    function init() {
        console.log('DealCarFilter: Инициализация');
        
        // 1. Начинаем наблюдать за выбором контакта
        watchForContactSelection();
        
        // 2. Настраиваем перехват запросов селектора
        interceptSelectorRequests();
        
        // 3. Проверяем, может контакт уже выбран
        setTimeout(() => {
            const existingContact = findContactId();
            if (existingContact) {
                currentContactId = existingContact;
                console.log('DealCarFilter: Контакт уже выбран при загрузке:', currentContactId);
            }
        }, 1000);
        
        console.log('DealCarFilter: Готов. Используй DealCarFilter.debug() для проверки');
    }
    
    // Запускаем
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();