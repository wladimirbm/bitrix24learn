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
        
        // 4. Настраиваем отслеживание перехода в режим редактирования
        setupEditModeObserver(carField);
        
        // 5. Отслеживаем изменения контакта
        setupContactObserver(contactField);
        
        // 6. Также отслеживаем через события Битрикс
        setupBitrixEvents();
        
        console.log('[Car Filter] Инициализация завершена (ожидание клика на поле)');
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
        
        // АЛЬТЕРНАТИВА: ищем по data-field-tag внутри
        carField = document.querySelector('[data-field-tag="UF_CRM_1770716463"]');
        if (carField) {
            console.log('[Car Filter] Поле найдено по data-field-tag');
            return carField.closest('[data-cid]');
        }
        
        // АЛЬТЕРНАТИВА 2: ищем по тексту метки "Авто"
        const labels = document.querySelectorAll('.ui-entity-editor-block-title-text');
        for (let label of labels) {
            if (label.textContent.includes('Авто') || 
                label.textContent.includes('авто') || 
                label.textContent.includes('Машина') || 
                label.textContent.includes('машина')) {
                
                const container = label.closest('[data-cid]');
                if (container) {
                    console.log('[Car Filter] Поле найдено по тексту метки:', label.textContent);
                    return container;
                }
            }
        }
        
        console.log('[Car Filter] Поле автомобиля не найдено');
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
        
        // 2. Из конфигурации (если передан при загрузке)
        if (config.contactId) {
            return config.contactId;
        }
        
        // 3. Из скрытого поля (если есть)
        const hiddenContactField = document.querySelector('input[name="CONTACT_ID"][type="hidden"]');
        if (hiddenContactField && hiddenContactField.value) {
            return hiddenContactField.value;
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
        
        // СПОСОБ 4: Ищем по data-field
        tileSelector = document.querySelector('[data-field="UF_CRM_1770716463"]');
        if (tileSelector) {
            console.log('[Car Filter] Tile selector найден по data-field');
            return tileSelector;
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
        
        // Получаем объект tile selector
        const tileInstance = getTileSelectorInstance(tileSelector);
        if (!tileInstance) {
            console.log('[Car Filter] Не удалось получить объект tile selector');
            return;
        }
        
        // Применяем фильтр
        applyFilterToTileSelector(tileInstance, contactId);
    }
    
    // Получает объект tile selector из DOM элемента
    function getTileSelectorInstance(tileElement) {
        // Способ 1: из свойства _instance
        if (tileElement._instance) {
            return tileElement._instance;
        }
        
        // Способ 2: через BX.UI.TileSelectorManager
        if (typeof BX !== 'undefined' && BX.UI && BX.UI.TileSelectorManager) {
            const selectorId = tileElement.id;
            if (selectorId) {
                return BX.UI.TileSelectorManager.getById(selectorId);
            }
        }
        
        // Способ 3: ищем в глобальных объектах BX
        if (window.BX && window.BX._components) {
            for (let key in window.BX._components) {
                if (key.includes('TileSelector') && window.BX._components[key]._container === tileElement) {
                    return window.BX._components[key];
                }
            }
        }
        
        return null;
    }
    
    // Применяет фильтр к tile selector
    function applyFilterToTileSelector(tileInstance, contactId) {
        if (!tileInstance || !contactId) return;
        
        // Создаем фильтр
        const filter = {
            dynamic_1054: {
                filter: {
                    'CONTACT_ID': contactId
                },
                searchable: 'title'
            }
        };
        
        console.log('[Car Filter] Применяем фильтр:', filter);
        
        // Способ 1: через настройки поиска
        if (tileInstance._searchOptions) {
            tileInstance._searchOptions = Object.assign({}, tileInstance._searchOptions, filter);
            console.log('[Car Filter] Фильтр применен через _searchOptions');
        }
        
        // Способ 2: через настройки сущностей
        if (tileInstance._entityOptions) {
            tileInstance._entityOptions = Object.assign({}, tileInstance._entityOptions, filter);
            console.log('[Car Filter] Фильтр применен через _entityOptions');
        }
        
        // Способ 3: через параметры
        if (tileInstance._params) {
            tileInstance._params.filter = tileInstance._params.filter || {};
            Object.assign(tileInstance._params.filter, filter.dynamic_1054.filter);
            console.log('[Car Filter] Фильтр применен через _params');
        }
        
        // Очищаем текущий выбор
        if (typeof tileInstance.clear === 'function') {
            tileInstance.clear();
        }
        
        // Перезагружаем данные
        if (typeof tileInstance._loadEntities === 'function') {
            tileInstance._loadEntities();
        }
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
                        
                        // Если tile selector уже открыт, обновляем фильтр
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
    
    // Настраивает обработку событий Битрикс
    function setupBitrixEvents() {
        if (typeof BX === 'undefined') return;
        
        // Событие изменения поля в редакторе
        BX.addCustomEvent('onCrmEntityEditorFieldValueChanged', function(event) {
            if (event.fieldName === 'CLIENT' || event.fieldName === 'CONTACT_ID') {
                const contactId = extractContactIdFromEvent(event);
                if (contactId && contactId !== window.currentContactId) {
                    console.log('[Car Filter] Событие Битрикс: контакт изменен', contactId);
                    window.currentContactId = contactId;
                }
            }
        });
        
        // Событие загрузки редактора
        BX.addCustomEvent('onCrmEntityEditorCreate', function(editor) {
            console.log('[Car Filter] Редактор создан');
        });
    }
    
    // Извлекает ID контакта из события Битрикс
    function extractContactIdFromEvent(event) {
        if (!event.value) return null;
        
        if (typeof event.value === 'string') {
            const match = event.value.match(/C_(\d+)|(\d+)/);
            return match ? (match[1] || match[2]) : null;
        }
        
        if (event.value.CONTACT_ID) {
            return event.value.CONTACT_ID;
        }
        
        return null;
    }
    
    // Запускаем инициализацию при загрузке страницы
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initCarFilter, 2000); // Ждем загрузки интерфейса
        });
    } else {
        setTimeout(initCarFilter, 2000);
    }
    
    // Экспортируем функции для отладки
    window.DEAL_CAR_FILTER = {
        init: initCarFilter,
        getCurrentContactId: getCurrentContactId,
        updateFilter: setupTileSelectorFilter
    };
    
})();