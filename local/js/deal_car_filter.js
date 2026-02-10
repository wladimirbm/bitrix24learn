/**
 * Фильтрация автомобилей по контакту в сделке Битрикс24
 * Версия для правильного поиска tile selector
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
    
    // Основная функция инициализации
    function initCarFilter() {
        console.log('[Car Filter] Инициализация фильтра автомобилей');
        
        // 1. Находим поле контакта
        const contactField = findContactField();
        if (!contactField) {
            console.log('[Car Filter] Поле контакта не найдено, ждем 2 сек');
            setTimeout(initCarFilter, 2000);
            return;
        }
        
        console.log('[Car Filter] Поле контакта найдено');
        
        // 2. Получаем начальный контакт
        const initialContactId = getCurrentContactId();
        if (initialContactId) {
            console.log('[Car Filter] Начальный контакт:', initialContactId);
            window.currentContactId = initialContactId;
        }
        
        // 3. Настраиваем отслеживание изменений контакта
        setupContactObserver(contactField);
        
        // 4. Находим поле автомобиля
        const carField = findCarField();
        if (carField) {
            console.log('[Car Filter] Поле автомобиля найдено');
            setupEditModeObserver(carField);
        } else {
            console.log('[Car Filter] Поле автомобиля не найдено, будет найден позже');
        }
        
        // 5. Настраиваем глобальный перехват кликов для поиска tile selector
        setupGlobalClickObserver();
        
        console.log('[Car Filter] Инициализация завершена');
    }
    
    // Находит поле контакта
    function findContactField() {
        // Основной способ: по data-cid
        return document.querySelector('[data-cid="CLIENT"]');
    }
    
    // Получает ID текущего выбранного контакта
    function getCurrentContactId() {
        // Из ссылки на контакт
        const contactLink = document.querySelector('a[href*="/crm/contact/details/"]');
        if (contactLink) {
            const match = contactLink.href.match(/\/crm\/contact\/details\/(\d+)/);
            if (match) return match[1];
        }
        
        return null;
    }
    
    // Находит поле автомобиля (блок с data-cid)
    function findCarField() {
        return document.querySelector('[data-cid="UF_CRM_1770716463"]');
    }
    
    // Отслеживает переход в режим редактирования поля
    function setupEditModeObserver(carField) {
        console.log('[Car Filter] Настройка наблюдателя за полем авто');
        
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    const target = mutation.target;
                    
                    if (target.classList.contains('ui-entity-editor-content-block-edit')) {
                        console.log('[Car Filter] Поле перешло в режим редактирования');
                        
                        // Ищем tile selector через 300мс
                        setTimeout(findAndSetupTileSelector, 300);
                    }
                }
            });
        });
        
        observer.observe(carField, {
            attributes: true,
            attributeFilter: ['class']
        });
        
        // Также отслеживаем клик
        carField.addEventListener('click', function() {
            console.log('[Car Filter] Клик по полю авто');
            setTimeout(findAndSetupTileSelector, 500);
        });
    }
    
    // Глобальный наблюдатель кликов для поиска tile selector
    function setupGlobalClickObserver() {
        console.log('[Car Filter] Настройка глобального наблюдателя кликов');
        
        document.addEventListener('click', function(e) {
            // Проверяем, кликнули ли на поле авто или его элементы
            const target = e.target;
            const isCarFieldClick = target.closest('[data-cid="UF_CRM_1770716463"]') || 
                                    target.closest('[id*="1770716463"]') ||
                                    (target.classList && (
                                        target.classList.contains('ui-tile-selector-select') ||
                                        target.classList.contains('ui-tile-selector-input') ||
                                        target.classList.contains('ui-tile-selector-select-container')
                                    ));
            
            if (isCarFieldClick) {
                console.log('[Car Filter] Клик по элементу поля авто');
                setTimeout(findAndSetupTileSelector, 400);
            }
        });
    }
    
    // Ищет и настраивает tile selector
    function findAndSetupTileSelector() {
        console.log('[Car Filter] Поиск tile selector...');
        
        // СПОСОБ 1: Ищем по ID с кодом поля
        let tileSelector = document.querySelector('[id*="uf-crm-1770716463"]');
        if (tileSelector && tileSelector.classList.contains('ui-tile-selector-selector-wrap')) {
            console.log('[Car Filter] Tile selector найден по ID');
            setupTileSelectorFilter(tileSelector);
            return;
        }
        
        // СПОСОБ 2: Ищем все tile selectors
        const allTileSelectors = document.querySelectorAll('.ui-tile-selector-selector-wrap');
        console.log('[Car Filter] Все tile selectors на странице:', allTileSelectors.length);
        
        if (allTileSelectors.length > 0) {
            // Берем последний (скорее всего активный)
            tileSelector = allTileSelectors[allTileSelectors.length - 1];
            console.log('[Car Filter] Используем последний tile selector');
            setupTileSelectorFilter(tileSelector);
            return;
        }
        
        // СПОСОБ 3: Ищем по input с ID содержащим код поля
        const input = document.querySelector('input[id*="1770716463"]');
        if (input) {
            tileSelector = input.closest('.ui-tile-selector-selector-wrap');
            if (tileSelector) {
                console.log('[Car Filter] Tile selector найден через input');
                setupTileSelectorFilter(tileSelector);
                return;
            }
        }
        
        console.log('[Car Filter] Tile selector не найден');
    }
    
    // Настраивает фильтр для tile selector
    function setupTileSelectorFilter(tileSelector) {
        console.log('[Car Filter] Настройка фильтра для tile selector');
        
        const contactId = window.currentContactId || getCurrentContactId();
        if (!contactId) {
            console.log('[Car Filter] Контакт не выбран');
            return;
        }
        
        console.log('[Car Filter] Применяем фильтр для контакта:', contactId);
        
        // 1. Прямое применение через модификацию объекта
        modifyTileSelectorObject(tileSelector, contactId);
        
        // 2. Добавляем data-атрибут
        tileSelector.setAttribute('data-contact-filter', contactId);
        
        // 3. Перехватываем AJAX
        interceptAjaxRequests(contactId);
        
        console.log('[Car Filter] Фильтр применен');
    }
    
    // Модифицирует объект tile selector
    function modifyTileSelectorObject(tileElement, contactId) {
        console.log('[Car Filter] Модификация объекта tile selector');
        
        // Находим input
        const input = tileElement.querySelector('input[type="text"]');
        if (!input) {
            console.log('[Car Filter] Input не найден');
            return;
        }
        
        // Проверяем autocomplete объект
        if (input._AC) {
            console.log('[Car Filter] Найден autocomplete объект');
            
            // Добавляем фильтр в параметры
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
            
            return;
        }
        
        // Пробуем получить объект через BX
        if (typeof BX !== 'undefined') {
            const selectorId = tileElement.id;
            if (selectorId && BX.UI && BX.UI.TileSelectorManager) {
                const tileInstance = BX.UI.TileSelectorManager.getById(selectorId);
                if (tileInstance) {
                    console.log('[Car Filter] Объект найден через BX.UI.TileSelectorManager');
                    
                    // Добавляем фильтр
                    if (tileInstance._searchOptions) {
                        tileInstance._searchOptions.dynamic_1054 = tileInstance._searchOptions.dynamic_1054 || {};
                        tileInstance._searchOptions.dynamic_1054.filter = { 'CONTACT_ID': contactId };
                    }
                    
                    if (tileInstance._entityOptions) {
                        tileInstance._entityOptions.dynamic_1054 = tileInstance._entityOptions.dynamic_1054 || {};
                        tileInstance._entityOptions.dynamic_1054.filter = { 'CONTACT_ID': contactId };
                    }
                    
                    return;
                }
            }
        }
        
        console.log('[Car Filter] Не удалось модифицировать объект tile selector');
    }
    
    // Перехватывает AJAX запросы
    function interceptAjaxRequests(contactId) {
        if (typeof BX === 'undefined' || !BX.ajax) {
            console.log('[Car Filter] BX.ajax не доступен');
            return;
        }
        
        console.log('[Car Filter] Настройка перехвата AJAX запросов');
        
        // Сохраняем оригинальный метод
        const originalAjax = BX.ajax;
        
        // Перехватываем
        BX.ajax = function(config) {
            // Проверяем, это ли запрос селектора
            if (config.url && config.url.includes('/bitrix/services/main/ajax.php')) {
                
                let shouldModify = false;
                let requestData = null;
                
                // Парсим данные запроса
                if (typeof config.data === 'string') {
                    try {
                        const params = new URLSearchParams(config.data);
                        const action = params.get('action');
                        if (action === 'getData' || action === 'search') {
                            requestData = JSON.parse(params.get('data') || '{}');
                            shouldModify = true;
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
                    if (dataStr.includes('dynamic_1054') || dataStr.includes('1770716463')) {
                        console.log('[Car Filter] Перехвачен запрос селектора автомобилей');
                        
                        // Добавляем фильтр
                        if (requestData.entities && requestData.entities.includes('dynamic_1054')) {
                            requestData.dynamic_1054 = requestData.dynamic_1054 || {};
                            requestData.dynamic_1054.filter = { 'CONTACT_ID': contactId };
                            console.log('[Car Filter] Фильтр добавлен в запрос');
                        }
                        
                        // Обновляем данные в запросе
                        if (typeof config.data === 'string') {
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
                        
                        // Обновляем фильтр
                        setTimeout(findAndSetupTileSelector, 100);
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
        getCurrentContactId: getCurrentContactId
    };
    
})();