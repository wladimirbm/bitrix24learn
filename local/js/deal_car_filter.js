/**
 * Фильтрация автомобилей по контакту в сделке Битрикс24
 * Версия для работы в iframe/slider
 */

(function() {
    // Защита от многократного запуска
    if (window.DEAL_CAR_FILTER_LOADED) {
        return;
    }
    window.DEAL_CAR_FILTER_LOADED = true;
    
    console.log('[Car Filter] Загрузка скрипта (защита от дублей)');
    
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
        
        // Проверяем, что мы в iframe сделки
        if (!isDealPage()) {
            console.log('[Car Filter] Не страница сделки, пропускаем');
            return;
        }
        
        // 1. Находим поле контакта
        const contactField = findContactField();
        if (!contactField) {
            console.log('[Car Filter] Поле контакта не найдено, ждем...');
            // Ожидаем только 5 секунд, потом останавливаем
            setTimeout(function() {
                const contactFieldRetry = findContactField();
                if (!contactFieldRetry) {
                    console.log('[Car Filter] Поле контакта так и не найдено, останавливаем');
                    return;
                }
                continueInit(contactFieldRetry);
            }, 1000);
            return;
        }
        
        continueInit(contactField);
    }
    
    // Продолжение инициализации после нахождения контакта
    function continueInit(contactField) {
        console.log('[Car Filter] Поле контакта найдено:', contactField);
        
        // 2. Находим поле автомобиля
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
        
        // 4. Если поле УЖЕ в режиме редактирования
        if (carField.classList.contains('ui-entity-editor-content-block-edit')) {
            console.log('[Car Filter] Поле уже в режиме редактирования');
            setTimeout(function() {
                const tileSelector = findTileSelectorInEditMode();
                if (tileSelector) {
                    setupTileSelectorFilter(tileSelector);
                }
            }, 800);
        }
        
        // 5. Настраиваем отслеживание перехода в режим редактирования
        setupEditModeObserver(carField);
        
        // 6. Отслеживаем изменения контакта
        setupContactObserver(contactField);
        
        console.log('[Car Filter] Инициализация завершена');
    }
    
    // Проверяем, что мы на странице сделки
    function isDealPage() {
        // Проверяем URL
        const url = window.location.href;
        return url.includes('/crm/deal/') || 
               url.includes('IFRAME=Y') || 
               document.querySelector('[data-cid="CLIENT"]') !== null;
    }
    
    // Находит поле контакта в виджете CLIENT
    function findContactField() {
        // Основной способ: по data-cid
        let contactBlock = document.querySelector('[data-cid="CLIENT"]');
        
        if (!contactBlock) {
            // Дополнительные способы поиска
            contactBlock = document.querySelector('.crm-entity-widget-client-block') ||
                          document.querySelector('.crm-entity-widget-participants-block');
        }
        
        return contactBlock;
    }
    
    // Находит поле автомобиля
    function findCarField() {
        console.log('[Car Filter] Поиск поля автомобиля...');
        
        // По data-cid
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
                    
                    if (target.classList.contains('ui-entity-editor-content-block-edit')) {
                        console.log('[Car Filter] Поле перешло в режим редактирования');
                        
                        setTimeout(function() {
                            const tileSelector = findTileSelectorInEditMode();
                            if (tileSelector) {
                                setupTileSelectorFilter(tileSelector);
                            }
                        }, 500);
                    }
                }
            });
        });
        
        observer.observe(carField, {
            attributes: true,
            attributeFilter: ['class']
        });
        
        // Отслеживаем клик
        carField.addEventListener('click', function() {
            console.log('[Car Filter] Клик по полю авто');
            setTimeout(function() {
                const tileSelector = findTileSelectorInEditMode();
                if (tileSelector) {
                    setupTileSelectorFilter(tileSelector);
                }
            }, 600);
        });
    }
    
    // Ищет tile selector в режиме редактирования
    function findTileSelectorInEditMode() {
        console.log('[Car Filter] Поиск tile selector');
        
        // Ищем внутри блока с data-cid
        const carBlock = document.querySelector('[data-cid="UF_CRM_1770716463"]');
        if (carBlock) {
            const tileSelector = carBlock.querySelector('.ui-tile-selector-selector-wrap');
            if (tileSelector) {
                console.log('[Car Filter] Tile selector найден');
                return tileSelector;
            }
        }
        
        // Ищем по всему документу
        const allSelectors = document.querySelectorAll('.ui-tile-selector-selector-wrap');
        if (allSelectors.length > 0) {
            console.log('[Car Filter] Найдено tile selectors:', allSelectors.length);
            return allSelectors[0];
        }
        
        console.log('[Car Filter] Tile selector не найден');
        return null;
    }
    
    // Настраивает фильтр для tile selector
    function setupTileSelectorFilter(tileSelector) {
        console.log('[Car Filter] Настройка фильтра');
        
        const contactId = window.currentContactId || getCurrentContactId();
        if (!contactId) {
            console.log('[Car Filter] Контакт не выбран');
            return;
        }
        
        console.log('[Car Filter] Применяем фильтр для контакта:', contactId);
        
        // Прямое применение фильтра
        applyFilterDirectly(tileSelector, contactId);
    }
    
    // Прямое применение фильтра
    function applyFilterDirectly(tileElement, contactId) {
        console.log('[Car Filter] Прямое применение фильтра');
        
        // 1. Находим input
        const input = tileElement.querySelector('input[type="text"]');
        if (!input) {
            console.log('[Car Filter] Input не найден');
            return;
        }
        
        // 2. Проверяем autocomplete объект
        if (input._AC) {
            console.log('[Car Filter] Найден autocomplete объект');
            
            // Модифицируем параметры
            if (input._AC._params) {
                input._AC._params.filter = input._AC._params.filter || {};
                input._AC._params.filter['CONTACT_ID'] = contactId;
                console.log('[Car Filter] Фильтр в _AC._params');
            }
            
            if (input._AC._searchOptions) {
                input._AC._searchOptions.dynamic_1054 = input._AC._searchOptions.dynamic_1054 || {};
                input._AC._searchOptions.dynamic_1054.filter = { 'CONTACT_ID': contactId };
                console.log('[Car Filter] Фильтр в _AC._searchOptions');
            }
            
            // Очищаем
            if (typeof input._AC.clearItems === 'function') {
                input._AC.clearItems();
            }
        }
        
        // 3. Добавляем data-атрибут
        tileElement.setAttribute('data-filter', JSON.stringify({
            dynamic_1054: { filter: { 'CONTACT_ID': contactId } }
        }));
        
        // 4. Перехватываем AJAX
        interceptAjaxRequests(contactId);
    }
    
    // Перехватывает AJAX запросы
    function interceptAjaxRequests(contactId) {
        if (typeof BX === 'undefined' || !BX.ajax) return;
        
        console.log('[Car Filter] Настройка перехвата AJAX');
        
        const originalAjax = BX.ajax;
        
        BX.ajax = function(config) {
            if (config.url && config.url.includes('/bitrix/services/main/ajax.php') && 
                config.data && (config.data.action === 'getData' || config.data.action === 'search')) {
                
                const dataStr = JSON.stringify(config.data);
                if (dataStr.includes('dynamic_1054')) {
                    console.log('[Car Filter] Перехвачен запрос для dynamic_1054');
                    
                    if (typeof config.data === 'string') {
                        try {
                            const params = new URLSearchParams(config.data);
                            let data = JSON.parse(params.get('data') || '{}');
                            
                            if (data.entities && data.entities.includes('dynamic_1054')) {
                                data.dynamic_1054 = data.dynamic_1054 || {};
                                data.dynamic_1054.filter = { 'CONTACT_ID': contactId };
                                params.set('data', JSON.stringify(data));
                                config.data = params.toString();
                            }
                        } catch(e) {}
                    } else if (typeof config.data === 'object') {
                        config.data.data = config.data.data || {};
                        if (config.data.data.entities && config.data.data.entities.includes('dynamic_1054')) {
                            config.data.data.dynamic_1054 = config.data.data.dynamic_1054 || {};
                            config.data.data.dynamic_1054.filter = { 'CONTACT_ID': contactId };
                        }
                    }
                }
            }
            
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
                        
                        const tileSelector = findTileSelectorInEditMode();
                        if (tileSelector) {
                            setupTileSelectorFilter(tileSelector);
                        }
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
        getCurrentContactId: getCurrentContactId
    };
    
})();