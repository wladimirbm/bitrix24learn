/**
 * Фильтрация автомобилей по выбранному контакту в сделке
 * Автоматически добавляет фильтр =CONTACT_ID в запросы селектора "Автомобиль"
 */

(function() {
    'use strict';
    
    // ID типа смарт-процесса "Гараж" (из вопроса)
    const SMART_PROCESS_TYPE_ID = 1054;
    
    // Идентификатор сущности для фильтра в запросе
    const ENTITY_CODE = 'DYNAMICS_' + SMART_PROCESS_TYPE_ID;
    
    // Переменная для хранения ID текущего выбранного контакта
    let currentContactId = null;
    
    // Observer для отслеживания изменений
    let clientObserver = null;
    
    /**
     * Инициализация модуля
     */
    function init() {
        // Ждем полной загрузки DOM
        BX.ready(function() {
            // 1. Находим блок "Клиент"
            const clientBlock = document.querySelector('[data-cid="CLIENT"]');
            
            if (!clientBlock) {
                console.warn('DealCarFilter: Блок клиента не найден');
                setTimeout(init, 1000);
                return;
            }
            
            // 2. Начинаем отслеживание изменений в блоке клиента
            startObservingClientBlock(clientBlock);
            
            // 3. Перехватываем запросы селектора "Автомобиль"
            interceptSelectorRequests();
            
            // 4. Ищем уже выбранный контакт
            checkInitialContact(clientBlock);
            
            console.log('DealCarFilter: Модуль инициализирован');
        });
    }
    
    /**
     * Проверка, выбран ли контакт при первоначальной загрузке страницы
     */
    function checkInitialContact(clientBlock) {
        const contactBadge = clientBlock.querySelector('.crm-entity-widget-img-contact + .crm-entity-widget-badge');
        if (contactBadge) {
            const entityId = contactBadge.getAttribute('data-entity-id');
            if (entityId) {
                currentContactId = entityId;
                console.log('DealCarFilter: Найден начальный контакт ID:', currentContactId);
            }
        }
    }
    
    /**
     * Сброс кеша селектора "Автомобиль"
     */
    function resetCarSelectorCache() {
        console.log('DealCarFilter: Сброс кеша селектора автомобилей');
        
        // Находим все селекторы, связанные с полем "Автомобиль"
        const carSelectorElements = document.querySelectorAll('[id*="UF_CRM_1770588718"], [data-field-id="UF_CRM_1770588718"]');
        
        carSelectorElements.forEach(function(element) {
            const selectorId = element.id;
            
            // Закрываем попап, если открыт
            let selectorInstance = null;
            if (window.BX && BX.UI && BX.UI.SelectorManager && BX.UI.SelectorManager.instances) {
                selectorInstance = BX.UI.SelectorManager.instances[selectorId];
            }
            
            if (!selectorInstance && window.BX && BX.Main && BX.Main.selectorManager) {
                selectorInstance = BX.Main.selectorManager.getById(selectorId);
            }
            
            if (selectorInstance && selectorInstance.closeDialog) {
                selectorInstance.closeDialog();
                console.log('DealCarFilter: Попап селектора закрыт', selectorId);
            }
            
            // Очищаем поле
            const hiddenInput = document.getElementById('UF_CRM_1770588718');
            if (hiddenInput) hiddenInput.value = '';
            
            const tileContainer = element.querySelector('[data-role="tile-container"]');
            if (tileContainer) {
                const items = tileContainer.querySelectorAll('[data-role="tile-item"]');
                items.forEach(item => item.remove());
            }
        });
        
        // Очищаем глобальный кеш
        clearGlobalSelectorCache();
    }
    
    /**
     * Очистка глобального кеша данных селектора
     */
    function clearGlobalSelectorCache() {
        const cacheStores = [
            window.BX && BX.Main && BX.Main.SelectorManager && BX.Main.SelectorManager.DataStore,
            window.BX && BX.UI && BX.UI.SelectorManager && BX.UI.SelectorManager.DataStore,
            window.BX && BX.Main && BX.Main.selectorManager && BX.Main.selectorManager.dataStore
        ];
        
        cacheStores.forEach(store => {
            if (store) {
                Object.keys(store).forEach(key => {
                    if (key.includes('DYNAMICS_1054') || key.includes('1054') || key.includes('UF_CRM_1770588718')) {
                        delete store[key];
                        console.log('DealCarFilter: Удален кеш:', key);
                    }
                });
            }
        });
    }
    
    /**
     * Отслеживание изменений в блоке "Клиент"
     */
    function startObservingClientBlock(clientBlock) {
        let previousContactId = currentContactId;
        
        clientObserver = new MutationObserver(function(mutations) {
            let contactChanged = false;
            
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'data-entity-id') {
                    contactChanged = true;
                }
                
                if (mutation.type === 'childList') {
                    const addedNodes = Array.from(mutation.addedNodes);
                    const removedNodes = Array.from(mutation.removedNodes);
                    
                    const contactBadge = clientBlock.querySelector('.crm-entity-widget-img-contact + .crm-entity-widget-badge');
                    if (contactBadge || removedNodes.some(node => node.classList && node.classList.contains('crm-entity-widget-badge'))) {
                        contactChanged = true;
                    }
                }
            });
            
            if (contactChanged) {
                setTimeout(function() {
                    const contactIcon = clientBlock.querySelector('.crm-entity-widget-img-contact');
                    if (!contactIcon) return;
                    
                    const contactBadge = contactIcon.nextElementSibling;
                    let newContactId = null;
                    
                    if (contactBadge && contactBadge.classList.contains('crm-entity-widget-badge')) {
                        newContactId = contactBadge.getAttribute('data-entity-id');
                    }
                    
                    if (newContactId !== previousContactId) {
                        previousContactId = newContactId;
                        currentContactId = newContactId;
                        
                        if (newContactId) {
                            console.log('DealCarFilter: Выбран контакт ID:', newContactId);
                        } else {
                            console.log('DealCarFilter: Контакт сброшен');
                        }
                        
                        resetCarSelectorCache();
                    }
                }, 100);
            }
        });
        
        clientObserver.observe(clientBlock, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['data-entity-id', 'class', 'style']
        });
    }
    
    /**
     * Перехват запросов селектора "Автомобиль"
     */
    function interceptSelectorRequests() {
        // 1. Перехват первоначальной загрузки данных (getData)
        BX.addCustomEvent('UI::Selector::Item:onBeforeLoad', function(control, params) {
            if (!isCarSelector(control, params)) return;
            console.log('DealCarFilter: Перехвачен запрос getData');
            applyContactFilter(params);
        });
        
        // 2. Перехват поисковых запросов (doSearch)
        BX.addCustomEvent('UI::Selector::onBeforeSearch', function(control, params) {
            if (!isCarSelector(control, params)) return;
            console.log('DealCarFilter: Перехвачен запрос doSearch');
            applyContactFilter(params);
        });
        
        // 3. Отладочное событие при открытии попапа
        BX.addCustomEvent('UI::Selector::onShow', function(control) {
            if (isCarSelector(control)) {
                console.log('DealCarFilter: Открыт попап селектора, контакт:', currentContactId);
            }
        });
    }
    
    /**
     * Применение фильтра по контакту к параметрам запроса
     */
    function applyContactFilter(params) {
        // Если контакт выбран - добавляем фильтр
        if (currentContactId) {
            if (!params.data.FILTER) {
                params.data.FILTER = {};
            }
            
            params.data.FILTER[ENTITY_CODE] = {
                '=CONTACT_ID': currentContactId
            };
            
            console.log('DealCarFilter: Добавлен фильтр для контакта', currentContactId, 'в', params.data.action || 'request');
        } else {
            if (params.data.FILTER && params.data.FILTER[ENTITY_CODE]) {
                delete params.data.FILTER[ENTITY_CODE];
                console.log('DealCarFilter: Фильтр сброшен (контакт не выбран)');
            }
        }
    }
    
    /**
     * Проверка, что это селектор поля "Автомобиль"
     */
    function isCarSelector(control, params) {
        // 1. По entityTypes в параметрах запроса
        if (params && params.data && params.data.entityTypes) {
            if (params.data.entityTypes[ENTITY_CODE]) {
                return true;
            }
        }
        
        // 2. По options в параметрах запроса
        if (params && params.data && params.data.options) {
            if (params.data.options['enableCrmDynamics'] && 
                params.data.options['enableCrmDynamics'][SMART_PROCESS_TYPE_ID.toString()] === 'Y') {
                return true;
            }
        }
        
        // 3. По ID контрола
        if (control && control.id) {
            if (control.id.indexOf('UF_CRM_1770588718') !== -1 ||
                control.id.indexOf('crm-uf-crm-1770588718') !== -1) {
                return true;
            }
        }
        
        // 4. По container элемента
        if (control && control.container) {
            const containerId = control.container.id || '';
            const containerHtml = control.container.outerHTML || '';
            
            if (containerId.indexOf('UF_CRM_1770588718') !== -1 ||
                containerHtml.indexOf('UF_CRM_1770588718') !== -1 ||
                containerHtml.indexOf('Автомобиль') !== -1) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Публичные методы
     */
    window.DealCarFilter = {
        getCurrentContact: function() {
            return currentContactId;
        },
        
        setContactForDebug: function(contactId) {
            const oldContactId = currentContactId;
            currentContactId = contactId;
            console.log('DealCarFilter: Установлен контакт для отладки:', contactId, '(был:', oldContactId, ')');
            resetCarSelectorCache();
            return currentContactId;
        },
        
        resetContact: function() {
            currentContactId = null;
            console.log('DealCarFilter: Контакт сброшен вручную');
            resetCarSelectorCache();
        },
        
        openCarSelector: function() {
            const selectorBtn = document.querySelector('[id*="UF_CRM_1770588718"] [data-role="tile-select"]');
            if (selectorBtn) {
                selectorBtn.click();
                console.log('DealCarFilter: Селектор автомобилей открыт принудительно');
            } else {
                console.warn('DealCarFilter: Кнопка открытия селектора не найдена');
            }
        },
        
        getStatus: function() {
            return {
                initialized: true,
                currentContactId: currentContactId,
                smartProcessType: SMART_PROCESS_TYPE_ID,
                entityCode: ENTITY_CODE,
                observerActive: !!clientObserver
            };
        },
        
        testAddFilter: function() {
            const testParams = {
                data: {
                    entityTypes: {},
                    action: 'doSearch'
                }
            };
            testParams.data.entityTypes[ENTITY_CODE] = { options: {} };
            
            const fakeControl = { id: 'test_UF_CRM_1770588718' };
            const testContactId = currentContactId || 999;
            
            const originalContactId = currentContactId;
            currentContactId = testContactId;
            
            applyContactFilter(testParams);
            
            currentContactId = originalContactId;
            
            console.log('DealCarFilter: Тест фильтра', testParams);
            return testParams;
        },
        
        reinit: function() {
            if (clientObserver) {
                clientObserver.disconnect();
                clientObserver = null;
            }
            
            currentContactId = null;
            init();
            console.log('DealCarFilter: Модуль переинициализирован');
        }
    };
    
    // Инициализируем модуль при загрузке
    init();
})();