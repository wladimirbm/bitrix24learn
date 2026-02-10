/**
 * Фильтрация автомобилей по контакту в сделке Битрикс24
 */

(function() {
    // Конфигурация
    const config = window.DEAL_CAR_FILTER_CONFIG || {
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
        
        if (!config.isEditPage) {
            console.log('[Car Filter] Не страница редактирования, пропускаем');
            return;
        }
        
        // 1. Находим поле контакта
        const contactField = findContactField();
        if (!contactField) {
            console.log('[Car Filter] Поле контакта не найдено, повтор через 1 сек');
            setTimeout(initCarFilter, 1000);
            return;
        }
        
        console.log('[Car Filter] Поле контакта найдено:', contactField);
        
        // 2. Находим поле автомобиля (в режиме просмотра)
        const carField = findCarField();
        if (!carField) {
            console.log('[Car Filter] Поле автомобиля не найдено');
            return;
        }
        
        console.log('[Car Filter] Поле автомобиля найдено:', carField);
        
        // 3. Получаем начальный контакт
        const initialContactId = getCurrentContactId();
        if (initialContactId) {
            console.log('[Car Filter] Начальный контакт:', initialContactId);
            window.currentContactId = initialContactId;
        }
        
        // 4. Если поле УЖЕ в режиме редактирования, сразу ищем tile selector
        if (carField.classList.contains('ui-entity-editor-content-block-edit')) {
            console.log('[Car Filter] Поле уже в режиме редактирования, ищем tile selector');
            setTimeout(function() {
                const tileSelector = findTileSelectorInEditMode();
                if (tileSelector) {
                    setupTileSelectorFilter(tileSelector);
                }
            }, 500);
        }
        
        // 5. Настраиваем отслеживание перехода в режим редактирования
        setupEditModeObserver(carField);
        
        // 6. Отслеживаем изменения контакта
        setupContactObserver(contactField);
        
        console.log('[Car Filter] Инициализация завершена');
    }
    
    // Находит поле контакта в виджете CLIENT
    function findContactField() {
        // Ищем по data-cid (режим просмотра)
        let contactBlock = document.querySelector('[data-cid="CLIENT"]');
        
        if (!contactBlock) {
            // Ищем в режиме редактирования
            contactBlock = document.querySelector('.crm-entity-widget-client-block');
        }
        
        return contactBlock;
    }
    
    // Находит поле автомобиля - для режима просмотра
    function findCarField() {
        console.log('[Car Filter] Поиск поля автомобиля...');
        
        // ОСНОВНОЙ СПОСОБ: по data-cid
        let carField = document.querySelector('[data-cid="UF_CRM_1770716463"]');
        if (carField) {
            console.log('[Car Filter] Поле найдено по data-cid');
            return carField;
        }
        
        return null;
    }
    
    // Получает ID текущего выбранного контакта
    function getCurrentContactId() {
        // 1. Из ссылки на контакт
        const contactLink = document.querySelector('a[href*="/crm/contact/details/"]');
        if (contactLink) {
            const match = contactLink.href.match(/\/crm\/contact\/details\/(\d+)/);
            if (match) return match[1];
        }
        
        return null;
    }
    
    // Отслеживает переход в режим редактирования поля
    function setupEditModeObserver(carField) {
        console.log('[Car Filter] Настройка наблюдателя за режимом редактирования');
        
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    const target = mutation.target;
                    
                    // Проверяем, перешел ли блок в режим редактирования
                    if (target.classList.contains('ui-entity-editor-content-block-edit')) {
                        console.log('[Car Filter] Поле перешло в режим редактирования');
                        
                        // Даем время на отрисовку tile selector
                        setTimeout(function() {
                            const tileSelector = findTileSelectorInEditMode();
                            if (tileSelector) {
                                setupTileSelectorFilter(tileSelector);
                            }
                        }, 300);
                    }
                }
            });
        });
        
        // Наблюдаем за изменениями класса
        observer.observe(carField, {
            attributes: true,
            attributeFilter: ['class']
        });
        
        // Также отслеживаем клик по полю
        carField.addEventListener('click', function() {
            console.log('[Car Filter] Клик по полю авто');
            // Через 500мс ищем tile selector
            setTimeout(function() {
                const tileSelector = findTileSelectorInEditMode();
                if (tileSelector) {
                    setupTileSelectorFilter(tileSelector);
                }
            }, 500);
        });
    }
    
    // Ищет tile selector в режиме редактирования
    function findTileSelectorInEditMode() {
        console.log('[Car Filter] Поиск tile selector в режиме редактирования');
        
        // Ищем активный tile selector
        let tileSelector = null;
        
        // СПОСОБ 1: Ищем внутри блока с data-cid
        const carBlock = document.querySelector('[data-cid="UF_CRM_1770716463"]');
        if (carBlock) {
            tileSelector = carBlock.querySelector('.ui-tile-selector-selector-wrap');
            if (tileSelector) {
                console.log('[Car Filter] Tile selector найден внутри data-cid блока');
                return tileSelector;
            }
        }
        
        // СПОСОБ 2: Ищем по ID с кодом поля
        const elements = document.querySelectorAll('[id*="1770716463"]');
        for (let element of elements) {
            if (element.classList.contains('ui-tile-selector-selector-wrap')) {
                console.log('[Car Filter] Tile selector найден по ID');
                return element;
            }
        }
        
        // СПОСОБ 3: Ищем все tile selectors
        const allTileSelectors = document.querySelectorAll('.ui-tile-selector-selector-wrap');
        if (allTileSelectors.length === 1) {
            console.log('[Car Filter] Найден единственный tile selector на странице');
            return allTileSelectors[0];
        }
        
        console.log('[Car Filter] Tile selector не найден в режиме редактирования');
        return null;
    }
    
    // Настраивает фильтр для tile selector
    function setupTileSelectorFilter(tileSelector) {
        console.log('[Car Filter] Настройка фильтра для tile selector');
        
        const contactId = window.currentContactId || getCurrentContactId();
        if (!contactId) {
            console.log('[Car Filter] Контакт не выбран, фильтр не применяется');
            return;
        }
        
        console.log('[Car Filter] Применяем фильтр для контакта:', contactId);
        
        // Пробуем несколько способов применения фильтра
        applyFilterDirectly(tileSelector, contactId);
        
        // Также пробуем получить объект tile selector
        const tileInstance = getTileSelectorInstance(tileSelector);
        if (tileInstance) {
            applyFilterToTileInstance(tileInstance, contactId);
        }
    }
    
    // Пытается получить объект tile selector из DOM элемента
    function getTileSelectorInstance(tileElement) {
        console.log('[Car Filter] Попытка получить объект tile selector');
        
        // Способ 1: из свойства _instance
        if (tileElement._instance) {
            console.log('[Car Filter] Объект найден в _instance');
            return tileElement._instance;
        }
        
        // Способ 2: через BX.UI.TileSelectorManager
        if (typeof BX !== 'undefined' && BX.UI && BX.UI.TileSelectorManager) {
            const selectorId = tileElement.id;
            if (selectorId) {
                const instance = BX.UI.TileSelectorManager.getById(selectorId);
                if (instance) {
                    console.log('[Car Filter] Объект найден через BX.UI.TileSelectorManager');
                    return instance;
                }
            }
        }
        
        // Способ 3: ищем input внутри и его autocomplete
        const input = tileElement.querySelector('input[type="text"]');
        if (input && input._AC) {
            console.log('[Car Filter] Найден autocomplete объекта');
            return input._AC;
        }
        
        // Способ 4: ищем в глобальном объекте BX
        if (window.BX && window.BX._components) {
            for (let key in window.BX._components) {
                const component = window.BX._components[key];
                if (component && component._container === tileElement) {
                    console.log('[Car Filter] Объект найден в BX._components');
                    return component;
                }
            }
        }
        
        console.log('[Car Filter] Не удалось получить объект tile selector');
        return null;
    }
    
    // Прямое применение фильтра через модификацию DOM и событий
    function applyFilterDirectly(tileElement, contactId) {
        console.log('[Car Filter] Прямое применение фильтра');
        
        // 1. Находим input внутри tile selector
        const input = tileElement.querySelector('input[type="text"]');
        if (!input) {
            console.log('[Car Filter] Input не найден внутри tile selector');
            return;
        }
        
        // 2. Проверяем, есть ли autocomplete объект
        if (input._AC) {
            console.log('[Car Filter] Найден autocomplete объект, модифицируем параметры');
            
            // Модифицируем параметры поиска
            if (input._AC._params) {
                input._AC._params.filter = input._AC._params.filter || {};
                input._AC._params.filter['CONTACT_ID'] = contactId;
                console.log('[Car Filter] Фильтр добавлен в _AC._params');
            }
            
            // Также пробуем модифицировать searchOptions
            if (input._AC._searchOptions) {
                input._AC._searchOptions.dynamic_1054 = input._AC._searchOptions.dynamic_1054 || {};
                input._AC._searchOptions.dynamic_1054.filter = { 'CONTACT_ID': contactId };
                console.log('[Car Filter] Фильтр добавлен в _AC._searchOptions');
            }
            
            // Очищаем текущие данные
            if (typeof input._AC.clearItems === 'function') {
                input._AC.clearItems();
            }
        }
        
        // 3. Модифицируем data-атрибуты
        const filterData = {
            dynamic_1054: {
                filter: { 'CONTACT_ID': contactId }
            }
        };
        
        tileElement.setAttribute('data-filter', JSON.stringify(filterData));
        console.log('[Car Filter] Добавлен data-filter атрибут');
        
        // 4. Добавляем обработчик для перехвата AJAX запросов
        interceptAjaxRequests(contactId);
    }
    
    // Применяет фильтр к объекту tile selector
    function applyFilterToTileInstance(tileInstance, contactId) {
        if (!tileInstance) return;
        
        console.log('[Car Filter] Применение фильтра к объекту tile selector');
        
        const filter = {
            dynamic_1054: {
                filter: { 'CONTACT_ID': contactId },
                searchable: 'title'
            }
        };
        
        // Пробуем разные свойства объекта
        const properties = ['_searchOptions', '_entityOptions', '_params', 'searchOptions', 'entityOptions', 'params'];
        
        for (let prop of properties) {
            if (tileInstance[prop]) {
                console.log('[Car Filter] Найдено свойство:', prop);
                
                if (prop === '_params' || prop === 'params') {
                    tileInstance[prop].filter = tileInstance[prop].filter || {};
                    Object.assign(tileInstance[prop].filter, filter.dynamic_1054.filter);
                } else {
                    tileInstance[prop] = Object.assign({}, tileInstance[prop], filter);
                }
                
                console.log('[Car Filter] Фильтр применен через свойство', prop);
                break;
            }
        }
        
        // Очищаем текущий выбор если есть метод
        if (typeof tileInstance.clear === 'function') {
            tileInstance.clear();
        }
        
        // Перезагружаем данные если есть метод
        if (typeof tileInstance._loadEntities === 'function') {
            tileInstance._loadEntities();
        }
    }
    
    // Перехватывает AJAX запросы селектора
    function interceptAjaxRequests(contactId) {
        if (typeof BX === 'undefined' || !BX.ajax) return;
        
        console.log('[Car Filter] Настройка перехвата AJAX запросов');
        
        // Сохраняем оригинальный метод
        const originalAjax = BX.ajax;
        
        // Перехватываем
        BX.ajax = function(config) {
            // Проверяем, это ли запрос селектора
            if (config.url && config.url.includes('/bitrix/services/main/ajax.php') && 
                config.data && (config.data.action === 'getData' || config.data.action === 'search')) {
                
                // Проверяем, относится ли запрос к нашему полю
                const dataStr = JSON.stringify(config.data);
                if (dataStr.includes('dynamic_1054') || dataStr.includes('1770716463')) {
                    
                    console.log('[Car Filter] Перехвачен AJAX запрос селектора');
                    
                    // Модифицируем данные запроса
                    if (typeof config.data === 'string') {
                        try {
                            const params = new URLSearchParams(config.data);
                            let data = JSON.parse(params.get('data') || '{}');
                            
                            // Добавляем фильтр
                            if (data.entities && data.entities.includes('dynamic_1054')) {
                                data.dynamic_1054 = data.dynamic_1054 || {};
                                data.dynamic_1054.filter = { 'CONTACT_ID': contactId };
                            }
                            
                            // Обновляем параметры
                            params.set('data', JSON.stringify(data));
                            config.data = params.toString();
                            console.log('[Car Filter] AJAX запрос модифицирован (string)');
                        } catch(e) {
                            console.error('[Car Filter] Ошибка парсинга:', e);
                        }
                    } else if (typeof config.data === 'object') {
                        // Если data - объект
                        config.data.data = config.data.data || {};
                        
                        // Добавляем фильтр
                        if (config.data.data.entities && config.data.data.entities.includes('dynamic_1054')) {
                            config.data.data.dynamic_1054 = config.data.data.dynamic_1054 || {};
                            config.data.data.dynamic_1054.filter = { 'CONTACT_ID': contactId };
                            console.log('[Car Filter] AJAX запрос модифицирован (object)');
                        }
                    }
                }
            }
            
            // Вызываем оригинальный метод
            return originalAjax.apply(this, arguments);
        };
    }
    
    // Настраивает отслеживание изменений контакта
    function setupContactObserver(contactField) {
        // Используем MutationObserver для отслеживания изменений DOM
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' || mutation.type === 'subtree') {
                    const contactId = getCurrentContactId();
                    if (contactId && contactId !== window.currentContactId) {
                        console.log('[Car Filter] Контакт изменен:', contactId);
                        window.currentContactId = contactId;
                        
                        // Обновляем фильтр если tile selector уже открыт
                        const tileSelector = findTileSelectorInEditMode();
                        if (tileSelector) {
                            setupTileSelectorFilter(tileSelector);
                        }
                    }
                }
            });
        });
        
        // Наблюдаем за изменениями в блоке контакта
        observer.observe(contactField, {
            childList: true,
            subtree: true,
            attributes: true,
            characterData: true
        });
        
        console.log('[Car Filter] Наблюдатель за контактом установлен');
    }
    
    // Запускаем инициализацию при загрузке страницы
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initCarFilter, 2000);
        });
    } else {
        setTimeout(initCarFilter, 2000);
    }
    
    // Экспортируем функции для отладки
    window.DEAL_CAR_FILTER = {
        init: initCarFilter,
        getCurrentContactId: getCurrentContactId,
        setupTileSelectorFilter: setupTileSelectorFilter
    };
    
})();