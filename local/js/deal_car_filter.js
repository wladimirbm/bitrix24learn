/**
 * Фильтрация автомобилей по контакту в сделке Битрикс24
 * Финальная версия с правильным поиском tile selector
 */

(function() {
    // Защита от многократного запуска
    if (window.DEAL_CAR_FILTER_LOADED) {
        return;
    }
    window.DEAL_CAR_FILTER_LOADED = true;
    
    console.log('[Car Filter] Загрузка скрипта');
    
    // Конфигурация
    const config = {
        garageEntityId: 1054,
        carFieldCode: 'UF_CRM_1770716463',
        contactId: null,
        isEditPage: true
    };
    
    // Глобальная переменная для хранения ID контакта
    window.currentContactId = null;
    // Флаг, что фильтр уже применен
    window.filterApplied = false;
    // ID последнего найденного tile selector
    window.lastTileSelectorId = null;
    
    // Основная функция инициализации
    function initCarFilter() {
        console.log('[Car Filter] Инициализация');
        
        // 1. Находим поле контакта
        const contactField = findContactField();
        if (!contactField) {
            console.log('[Car Filter] Поле контакта не найдено, повтор через 1 сек');
            setTimeout(initCarFilter, 1000);
            return;
        }
        
        console.log('[Car Filter] Поле контакта найдено');
        
        // 2. Получаем начальный контакт
        const initialContactId = getCurrentContactId();
        if (initialContactId) {
            console.log('[Car Filter] Начальный контакт:', initialContactId);
            window.currentContactId = initialContactId;
        }
        
        // 3. Находим поле автомобиля (основной блок)
        const carField = findCarField();
        if (carField) {
            console.log('[Car Filter] Поле автомобиля найдено');
            setupCarFieldObserver(carField);
        }
        
        // 4. Пытаемся сразу найти tile selector (он может уже быть в DOM)
        setTimeout(findAndSetupTileSelector, 500);
        
        // 5. Настраиваем отслеживание изменений контакта
        setupContactObserver(contactField);
        
        // 6. Настраиваем перехват AJAX запросов
        interceptAjaxRequests();
        
        console.log('[Car Filter] Инициализация завершена');
    }
    
    // Находит поле контакта
    function findContactField() {
        return document.querySelector('[data-cid="CLIENT"]');
    }
    
    // Находит поле автомобиля (основной блок)
    function findCarField() {
        return document.querySelector('[data-cid="UF_CRM_1770716463"]');
    }
    
    // Получает ID текущего выбранного контакта
    function getCurrentContactId() {
        const contactLink = document.querySelector('a[href*="/crm/contact/details/"]');
        if (contactLink) {
            const match = contactLink.href.match(/\/crm\/contact\/details\/(\d+)/);
            if (match) return match[1];
        }
        return null;
    }
    
    // Наблюдает за полем автомобиля
    function setupCarFieldObserver(carField) {
        console.log('[Car Filter] Настройка наблюдателя за полем авто');
        
        // Отслеживаем клик по полю
        carField.addEventListener('click', function() {
            console.log('[Car Filter] Клик по полю авто');
            setTimeout(findAndSetupTileSelector, 300);
        });
        
        // Также отслеживаем изменения класса (переход в режим редактирования)
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    const target = mutation.target;
                    if (target.classList.contains('ui-entity-editor-content-block-edit')) {
                        console.log('[Car Filter] Поле перешло в режим редактирования');
                        setTimeout(findAndSetupTileSelector, 400);
                    }
                }
            });
        });
        
        observer.observe(carField, {
            attributes: true,
            attributeFilter: ['class']
        });
    }
    
    // Ищет и настраивает tile selector
    function findAndSetupTileSelector() {
        console.log('[Car Filter] Поиск tile selector...');
        
        // ПРЯМОЙ ПОИСК по известному ID формату
        // ID имеет формат: ui-tile-selector-uf-crm-1770716463cODy3Kxxxxxxxx
        let tileSelector = null;
        
        // Способ 1: Ищем по части ID (1770716463)
        const selectorById = document.querySelector('[id*="1770716463"].ui-tile-selector-selector-wrap');
        if (selectorById) {
            tileSelector = selectorById;
            console.log('[Car Filter] Tile selector найден по части ID');
        }
        
        // Способ 2: Ищем все tile selectors
        if (!tileSelector) {
            const allSelectors = document.querySelectorAll('.ui-tile-selector-selector-wrap');
            if (allSelectors.length > 0) {
                tileSelector = allSelectors[allSelectors.length - 1];
                console.log('[Car Filter] Tile selector найден среди всех:', allSelectors.length);
            }
        }
        
        // Способ 3: Ищем по input внутри
        if (!tileSelector) {
            const input = document.querySelector('input[id*="1770716463"][type="text"]');
            if (input) {
                tileSelector = input.closest('.ui-tile-selector-selector-wrap');
                if (tileSelector) {
                    console.log('[Car Filter] Tile selector найден через input');
                }
            }
        }
        
        if (tileSelector) {
            // Проверяем, не тот ли это же селектор, что уже обрабатывали
            if (window.lastTileSelectorId === tileSelector.id && window.filterApplied) {
                console.log('[Car Filter] Этот tile selector уже обработан');
                return;
            }
            
            window.lastTileSelectorId = tileSelector.id;
            console.log('[Car Filter] Найден tile selector:', tileSelector.id);
            applyFilterToTileSelector(tileSelector);
        } else {
            console.log('[Car Filter] Tile selector не найден');
        }
    }
    
    // Применяет фильтр к tile selector
    function applyFilterToTileSelector(tileSelector) {
        if (!tileSelector) return;
        
        const contactId = window.currentContactId || getCurrentContactId();
        if (!contactId) {
            console.log('[Car Filter] Контакт не выбран');
            return;
        }
        
        console.log('[Car Filter] Применение фильтра для контакта:', contactId);
        
        // Помечаем как обработанный
        tileSelector.setAttribute('data-contact-filter', contactId);
        window.filterApplied = true;
        
        // 1. Модифицируем autocomplete объект
        modifyAutocompleteObject(tileSelector, contactId);
        
        // 2. Добавляем data-атрибут для отслеживания
        tileSelector.setAttribute('data-filter-applied', 'true');
        
        console.log('[Car Filter] Фильтр применен к tile selector');
    }
    
    // Модифицирует autocomplete объект
    function modifyAutocompleteObject(tileSelector, contactId) {
        // Находим input
        const input = tileSelector.querySelector('input[type="text"]');
        if (!input) {
            console.log('[Car Filter] Input не найден в tile selector');
            return;
        }
        
        console.log('[Car Filter] Input найден:', input.id);
        
        // Ждем инициализации autocomplete объекта
        const checkAutocomplete = function(retryCount = 0) {
            if (input._AC) {
                console.log('[Car Filter] Autocomplete объект найден');
                
                // Модифицируем параметры
                if (!input._AC._params) {
                    input._AC._params = {};
                }
                input._AC._params.filter = input._AC._params.filter || {};
                input._AC._params.filter['CONTACT_ID'] = contactId;
                
                // Также в searchOptions если есть
                if (input._AC._searchOptions) {
                    input._AC._searchOptions.dynamic_1054 = input._AC._searchOptions.dynamic_1054 || {};
                    input._AC._searchOptions.dynamic_1054.filter = { 'CONTACT_ID': contactId };
                }
                
                // Очищаем текущие данные
                if (typeof input._AC.clearItems === 'function') {
                    input._AC.clearItems();
                    console.log('[Car Filter] Данные autocomplete очищены');
                }
                
                // Принудительно запускаем поиск если есть метод
                if (typeof input._AC._search === 'function') {
                    setTimeout(function() {
                        input._AC._search();
                    }, 200);
                }
                
            } else if (retryCount < 5) {
                // Пробуем еще раз
                console.log('[Car Filter] Autocomplete объект еще не готов, повтор:', retryCount + 1);
                setTimeout(function() {
                    checkAutocomplete(retryCount + 1);
                }, 300);
            } else {
                console.log('[Car Filter] Не удалось дождаться autocomplete объекта');
            }
        };
        
        // Запускаем проверку
        checkAutocomplete();
    }
    
    // Отслеживает изменения контакта
    function setupContactObserver(contactField) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' || mutation.type === 'subtree') {
                    const contactId = getCurrentContactId();
                    if (contactId && contactId !== window.currentContactId) {
                        console.log('[Car Filter] Контакт изменен:', contactId);
                        window.currentContactId = contactId;
                        window.filterApplied = false; // Сбрасываем флаг
                        
                        // Обновляем фильтр
                        setTimeout(findAndSetupTileSelector, 200);
                    }
                }
            });
        });
        
        observer.observe(contactField, {
            childList: true,
            subtree: true,
            attributes: true,
            characterData: true
        });
        
        console.log('[Car Filter] Наблюдатель за контактом установлен');
    }
    
    // Перехватывает AJAX запросы
    function interceptAjaxRequests() {
        if (typeof BX === 'undefined' || !BX.ajax) {
            console.log('[Car Filter] BX.ajax не доступен');
            return;
        }
        
        console.log('[Car Filter] Настройка перехвата AJAX запросов');
        
        // Сохраняем оригинальный метод
        const originalAjax = BX.ajax;
        
        // Перехватываем
        BX.ajax = function(config) {
            const contactId = window.currentContactId || getCurrentContactId();
            
            if (!contactId) {
                return originalAjax.apply(this, arguments);
            }
            
            // Проверяем, это ли запрос селектора
            if (config.url && config.url.includes('/bitrix/services/main/ajax.php')) {
                
                let shouldModify = false;
                let requestData = null;
                let isStringData = false;
                
                // Парсим данные запроса
                if (typeof config.data === 'string') {
                    try {
                        const params = new URLSearchParams(config.data);
                        const action = params.get('action');
                        if (action === 'getData' || action === 'search') {
                            requestData = JSON.parse(params.get('data') || '{}');
                            shouldModify = true;
                            isStringData = true;
                        }
                    } catch(e) {}
                } else if (typeof config.data === 'object' && config.data.action) {
                    if (config.data.action === 'getData' || config.data.action === 'search') {
                        requestData = config.data.data || {};
                        shouldModify = true;
                    }
                }
                
                // Модифицируем если нужно
                if (shouldModify && requestData) {
                    const dataStr = JSON.stringify(requestData);
                    
                    // Проверяем, относится ли запрос к dynamic_1054
                    if (dataStr.includes('dynamic_1054')) {
                        console.log('[Car Filter] Перехвачен запрос для dynamic_1054');
                        
                        // Добавляем фильтр по контакту
                        if (requestData.entities && requestData.entities.includes('dynamic_1054')) {
                            requestData.dynamic_1054 = requestData.dynamic_1054 || {};
                            requestData.dynamic_1054.filter = { 'CONTACT_ID': contactId };
                            console.log('[Car Filter] Фильтр CONTACT_ID добавлен');
                        }
                        
                        // Также проверяем другие возможные структуры
                        if (requestData.dynamic_1054) {
                            requestData.dynamic_1054.filter = requestData.dynamic_1054.filter || {};
                            requestData.dynamic_1054.filter['CONTACT_ID'] = contactId;
                        }
                        
                        // Обновляем данные в запросе
                        if (isStringData) {
                            const params = new URLSearchParams(config.data);
                            params.set('data', JSON.stringify(requestData));
                            config.data = params.toString();
                        } else {
                            config.data.data = requestData;
                        }
                    }
                }
            }
            
            // Вызываем оригинальный метод
            return originalAjax.apply(this, arguments);
        };
        
        console.log('[Car Filter] Перехват AJAX настроен');
    }
    
    // Запуск
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initCarFilter, 1500);
        });
    } else {
        setTimeout(initCarFilter, 1500);
    }
    
    // Для отладки
    window.DEAL_CAR_FILTER = {
        init: initCarFilter,
        findAndSetupTileSelector: findAndSetupTileSelector,
        getCurrentContactId: getCurrentContactId,
        applyFilter: applyFilterToTileSelector
    };
    
})();