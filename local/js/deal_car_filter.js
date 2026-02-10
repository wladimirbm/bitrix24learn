/**
 * Фильтрация автомобилей по выбранному контакту в сделке
 * Автоматически добавляет фильтр =CONTACT_ID в запросы селектора "Автомобиль"
 */

(function() {
    'use strict';
    
    const SMART_PROCESS_TYPE_ID = 1054;
    const ENTITY_CODE = 'DYNAMICS_' + SMART_PROCESS_TYPE_ID;
    let currentContactId = null;
    let clientObserver = null;
    let initializationAttempts = 0;
    const MAX_INIT_ATTEMPTS = 10;
    
    /**
     * Инициализация модуля
     */
    function init() {
        initializationAttempts++;
        
        // Ждем полной загрузки DOM
        BX.ready(function() {
            console.log('DealCarFilter: Инициализация, попытка', initializationAttempts);
            
            // 1. Находим блок "Клиент" - ПРОБУЕМ РАЗНЫЕ СЕЛЕКТОРЫ
            const clientBlock = findClientBlock();
            
            if (!clientBlock) {
                console.warn('DealCarFilter: Блок клиента не найден, текущий URL:', window.location.pathname);
                
                // Если страница все еще загружается - пробуем еще раз
                if (initializationAttempts < MAX_INIT_ATTEMPTS) {
                    setTimeout(init, 500);
                } else {
                    console.error('DealCarFilter: Не удалось найти блок клиента после', MAX_INIT_ATTEMPTS, 'попыток');
                    
                    // Пробуем подключиться позже через событие загрузки страницы
                    BX.addCustomEvent(window, 'onCrmEntityEditorLoad', function() {
                        console.log('DealCarFilter: Событие onCrmEntityEditorLoad, повторная инициализация');
                        initializationAttempts = 0;
                        setTimeout(init, 300);
                    });
                }
                return;
            }
            
            console.log('DealCarFilter: Блок клиента найден:', clientBlock);
            
            // 2. Начинаем отслеживание изменений в блоке клиента
            startObservingClientBlock(clientBlock);
            
            // 3. Перехватываем запросы селектора "Автомобиль"
            interceptSelectorRequests();
            
            // 4. Ищем уже выбранный контакт
            checkInitialContact(clientBlock);
            
            // 5. Вешаем глобальные обработчики на динамическую загрузку
            setupGlobalEventHandlers();
            
            console.log('DealCarFilter: Модуль успешно инициализирован');
        });
    }
    
    /**
     * Поиск блока "Клиент" разными способами
     */
    function findClientBlock() {
        // Пробуем разные селекторы, которые могут содержать блок клиента
        
        // 1. По data-cid (из вашего HTML)
        let block = document.querySelector('[data-cid="CLIENT"]');
        if (block) return block;
        
        // 2. По классу блока клиента
        block = document.querySelector('.crm-entity-widget-content-block[data-field-type="client"]');
        if (block) return block;
        
        // 3. По тексту "Клиент" в заголовке
        const elementsWithClientText = document.querySelectorAll('.ui-entity-editor-block-title-text');
        for (let element of elementsWithClientText) {
            if (element.textContent && element.textContent.trim() === 'Клиент') {
                // Ищем родительский блок
                let parent = element.closest('.ui-entity-editor-content-block');
                if (parent) return parent;
            }
        }
        
        // 4. По наличию полей "Контакт" и "Компания"
        const contactFields = document.querySelectorAll('.crm-entity-widget-content-search-input[placeholder*="контакт"], .crm-entity-widget-content-search-input[placeholder*="Контакт"]');
        for (let field of contactFields) {
            let parentBlock = field.closest('.ui-entity-editor-content-block');
            if (parentBlock) return parentBlock;
        }
        
        // 5. По структуре CRM сделки (общий поиск)
        block = document.querySelector('.crm-entity-card-container, .crm-entity-wrap, .ui-entity-editor');
        if (block) {
            // Внутри контейнера ищем блок с клиентом
            const clientInside = block.querySelector('.crm-entity-widget-content-block-inner');
            if (clientInside) {
                return clientInside.closest('.ui-entity-editor-content-block') || clientInside;
            }
        }
        
        return null;
    }
    
    /**
     * Глобальные обработчики событий
     */
    function setupGlobalEventHandlers() {
        // Событие, которое срабатывает при AJAX-обновлении страницы
        BX.addCustomEvent('onAjaxSuccess', function() {
            console.log('DealCarFilter: AJAX обновление страницы, проверяем блоки');
            setTimeout(checkAndReinit, 1000);
        });
        
        // Событие при изменении URL (SPA навигация)
        BX.addCustomEvent(window, 'onHistoryStateChanged', function() {
            console.log('DealCarFilter: Изменен URL, переинициализация');
            setTimeout(init, 500);
        });
    }
    
    /**
     * Проверка и переинициализация
     */
    function checkAndReinit() {
        const clientBlock = findClientBlock();
        const carField = document.getElementById('UF_CRM_1770588718');
        
        if (clientBlock && carField) {
            console.log('DealCarFilter: Блоки найдены после AJAX');
            // Уже инициализировано
        } else if (!clientObserver) {
            console.log('DealCarFilter: Переинициализация после AJAX');
            initializationAttempts = 0;
            init();
        }
    }
    
    /**
     * Проверка, выбран ли контакт при первоначальной загрузке страницы
     */
    function checkInitialContact(clientBlock) {
        // Ищем значок контакта разными способами
        let contactBadge = clientBlock.querySelector('.crm-entity-widget-img-contact + .crm-entity-widget-badge');
        
        if (!contactBadge) {
            // Альтернативный поиск
            contactBadge = clientBlock.querySelector('[data-entity-id]');
        }
        
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
        
        // Находим поле "Автомобиль" разными способами
        const carFields = [
            document.getElementById('UF_CRM_1770588718'),
            ...document.querySelectorAll('[id*="UF_CRM_1770588718"]'),
            ...document.querySelectorAll('[name*="UF_CRM_1770588718"]'),
            ...document.querySelectorAll('[data-field-id*="UF_CRM_1770588718"]')
        ].filter(Boolean);
        
        carFields.forEach(function(field) {
            const element = field.nodeName === 'INPUT' ? field.parentElement : field;
            if (!element) return;
            
            // Закрываем попап селектора
            const selectorId = element.id || '';
            let selectorInstance = null;
            
            // Пробуем разные API для получения экземпляра селектора
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
            
            // Очищаем значение
            if (field.value) field.value = '';
        });
        
        // Очищаем видимые плитки
        const tileContainers = document.querySelectorAll('[data-role="tile-container"]');
        tileContainers.forEach(container => {
            const items = container.querySelectorAll('[data-role="tile-item"]');
            if (items.length > 0) {
                items.forEach(item => item.remove());
                console.log('DealCarFilter: Очищены плитки выбора');
            }
        });
        
        clearGlobalSelectorCache();
    }
    
    /**
     * Очистка глобального кеша данных селектора
     */
    function clearGlobalSelectorCache() {
        const cacheStores = [
            window.BX && BX.Main && BX.Main.SelectorManager && BX.Main.SelectorManager.DataStore,
            window.BX && BX.UI && BX.UI.SelectorManager && BX.UI.SelectorManager.DataStore,
            window.BX && BX.Main && BX.Main.selectorManager && BX.Main.selectorManager.dataStore,
            window.BXMainSelectorDataStore // Еще один возможный вариант
        ].filter(Boolean);
        
        cacheStores.forEach(store => {
            Object.keys(store).forEach(key => {
                if (key.includes('DYNAMICS_1054') || key.includes('1054') || key.includes('UF_CRM_1770588718')) {
                    delete store[key];
                    console.log('DealCarFilter: Удален кеш:', key);
                }
            });
        });
    }
    
    /**
     * Отслеживание изменений в блоке "Клиент"
     */
    function startObservingClientBlock(clientBlock) {
        let previousContactId = currentContactId;
        
        clientObserver = new MutationObserver(function(mutations) {
            let contactChanged = false;
            
            for (let mutation of mutations) {
                // Изменение атрибута data-entity-id
                if (mutation.type === 'attributes' && 
                    (mutation.attributeName === 'data-entity-id' || mutation.attributeName === 'class')) {
                    contactChanged = true;
                    break;
                }
                
                // Добавление/удаление элементов
                if (mutation.type === 'childList') {
                    const nodes = [...mutation.addedNodes, ...mutation.removedNodes];
                    const hasBadgeChanges = nodes.some(node => 
                        node.nodeType === 1 && 
                        (node.classList && node.classList.contains('crm-entity-widget-badge') ||
                         node.querySelector && node.querySelector('.crm-entity-widget-badge'))
                    );
                    
                    if (hasBadgeChanges) {
                        contactChanged = true;
                        break;
                    }
                }
            }
            
            if (contactChanged) {
                setTimeout(function() {
                    // Ищем текущий значок контакта
                    const contactBadge = clientBlock.querySelector('.crm-entity-widget-badge[data-entity-id]');
                    let newContactId = null;
                    
                    if (contactBadge) {
                        newContactId = contactBadge.getAttribute('data-entity-id');
                    } else {
                        // Проверяем, есть ли вообще значок (может контакт сброшен)
                        const anyBadge = clientBlock.querySelector('.crm-entity-widget-badge');
                        if (!anyBadge || !anyBadge.hasAttribute('data-entity-id')) {
                            newContactId = null;
                        }
                    }
                    
                    // Если контакт изменился
                    if (newContactId !== previousContactId) {
                        previousContactId = newContactId;
                        currentContactId = newContactId;
                        
                        console.log('DealCarFilter: Контакт изменен:', 
                            newContactId ? 'ID:' + newContactId : 'сброшен');
                        
                        // Сбрасываем кеш селектора
                        resetCarSelectorCache();
                    }
                }, 50); // Маленькая задержка для стабилизации DOM
            }
        });
        
        // Начинаем наблюдение
        clientObserver.observe(clientBlock, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['data-entity-id', 'class', 'style', 'placeholder']
        });
        
        // Также наблюдаем за всем документом на случай динамической подгрузки
        const globalObserver = new MutationObserver(function(mutations) {
            for (let mutation of mutations) {
                if (mutation.type === 'childList') {
                    const addedNodes = [...mutation.addedNodes];
                    const hasClientBlock = addedNodes.some(node => 
                        node.nodeType === 1 && 
                        (node.matches && node.matches('[data-cid="CLIENT"]')) ||
                        (node.querySelector && node.querySelector('[data-cid="CLIENT"]'))
                    );
                    
                    if (hasClientBlock) {
                        console.log('DealCarFilter: Динамически добавлен блок клиента');
                        setTimeout(init, 300);
                        break;
                    }
                }
            }
        });
        
        globalObserver.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    /**
     * Перехват запросов селектора "Автомобиль"
     */
    function interceptSelectorRequests() {
        console.log('DealCarFilter: Настройка перехвата запросов селектора');
        
        // Событие для первоначальной загрузки
        if (BX.addCustomEvent) {
            BX.addCustomEvent('UI::Selector::Item:onBeforeLoad', function(control, params) {
                if (!isCarSelector(control, params)) return;
                console.log('DealCarFilter: Перехвачен запрос getData');
                applyContactFilter(params);
            });
            
            // Событие для поиска
            BX.addCustomEvent('UI::Selector::onBeforeSearch', function(control, params) {
                if (!isCarSelector(control, params)) return;
                console.log('DealCarFilter: Перехвачен запрос doSearch');
                applyContactFilter(params);
            });
            
            // Событие при открытии
            BX.addCustomEvent('UI::Selector::onShow', function(control) {
                if (isCarSelector(control)) {
                    console.log('DealCarFilter: Открыт попап селектора, текущий контакт:', currentContactId);
                }
            });
        }
    }
    
    /**
     * Применение фильтра по контакту
     */
    function applyContactFilter(params) {
        if (!params || !params.data) return;
        
        if (currentContactId) {
            if (!params.data.FILTER) {
                params.data.FILTER = {};
            }
            
            params.data.FILTER[ENTITY_CODE] = {
                '=CONTACT_ID': currentContactId
            };
            
            console.log('DealCarFilter: Добавлен фильтр для контакта', currentContactId);
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
        if (!control) return false;
        
        // Проверка по параметрам запроса
        if (params && params.data) {
            // По entityTypes
            if (params.data.entityTypes && params.data.entityTypes[ENTITY_CODE]) {
                return true;
            }
            
            // По options
            if (params.data.options && params.data.options.enableCrmDynamics) {
                const dynamics = params.data.options.enableCrmDynamics;
                if (dynamics[SMART_PROCESS_TYPE_ID] === 'Y' || dynamics[SMART_PROCESS_TYPE_ID.toString()] === 'Y') {
                    return true;
                }
            }
        }
        
        // Проверка по ID и классам
        const element = control.container || control;
        if (!element) return false;
        
        const elementId = element.id || '';
        const elementHtml = element.outerHTML || element.innerHTML || '';
        
        // Ищем признаки поля "Автомобиль"
        const carFieldIndicators = [
            'UF_CRM_1770588718',
            'Автомобиль',
            'crm-uf-crm-1770588718',
            'dynamic_1054',
            'DYNAMICS_1054'
        ];
        
        return carFieldIndicators.some(indicator => 
            elementId.includes(indicator) || 
            elementHtml.includes(indicator)
        );
    }
    
    /**
     * Публичные методы для отладки
     */
    window.DealCarFilter = {
        getCurrentContact: function() {
            return currentContactId;
        },
        
        setContactForDebug: function(contactId) {
            currentContactId = contactId;
            console.log('DealCarFilter: Установлен контакт для отладки:', contactId);
            resetCarSelectorCache();
            return currentContactId;
        },
        
        resetContact: function() {
            currentContactId = null;
            console.log('DealCarFilter: Контакт сброшен');
            resetCarSelectorCache();
        },
        
        getStatus: function() {
            return {
                initialized: true,
                currentContactId: currentContactId,
                smartProcessType: SMART_PROCESS_TYPE_ID,
                entityCode: ENTITY_CODE,
                observerActive: !!clientObserver,
                initializationAttempts: initializationAttempts,
                pageUrl: window.location.pathname
            };
        },
        
        findClientBlock: function() {
            return findClientBlock();
        },
        
        reinit: function() {
            if (clientObserver) {
                clientObserver.disconnect();
                clientObserver = null;
            }
            
            initializationAttempts = 0;
            currentContactId = null;
            
            console.log('DealCarFilter: Принудительная переинициализация');
            setTimeout(init, 100);
        },
        
        testSelectorDetection: function() {
            const testElements = document.querySelectorAll('[id*="UF_CRM"], [data-field-id*="UF_CRM"]');
            console.log('DealCarFilter: Найдены элементы:', testElements.length);
            testElements.forEach(el => console.log('  -', el.id || el.dataset.fieldId, el));
        }
    };
    
    // Запуск при полной загрузке страницы
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(init, 500);
        });
    } else {
        setTimeout(init, 500);
    }
    
    // Дополнительный запуск через 2 секунды на случай динамической загрузки
    setTimeout(function() {
        if (!clientObserver && initializationAttempts === 0) {
            console.log('DealCarFilter: Запуск по таймауту');
            init();
        }
    }, 2000);
})();