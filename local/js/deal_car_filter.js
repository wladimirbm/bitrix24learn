/**
 * Фильтрация автомобилей по контакту в сделке Битрикс24
 * Автоматически обновляет фильтр в поле выбора автомобиля при изменении контакта
 */

(function() {
    // Конфигурация из компонента или значения по умолчанию
    const config = window.DEAL_CAR_FILTER_CONFIG || {
        garageEntityId: 1054,
        carFieldCode: 'UF_CRM_1770716463',
        contactId: null,
        isEditPage: true
    };
    
    // Основная функция инициализации
    function initCarFilter() {
        console.log('[Car Filter] Инициализация фильтра автомобилей');
        
        if (!config.isEditPage) {
            console.log('[Car Filter] Не страница редактирования, пропускаем');
            return;
        }
        
        // 1. Находим поле контакта в виджете клиента
        const contactField = findContactField();
        if (!contactField) {
            console.log('[Car Filter] Поле контакта не найдено, повтор через 1 сек');
            setTimeout(initCarFilter, 1000);
            return;
        }
        
        console.log('[Car Filter] Поле контакта найдено:', contactField);
        
        // 2. Находим поле автомобиля (tile selector)
        const carField = findCarField();
        if (!carField) {
            console.log('[Car Filter] Поле автомобиля не найдено');
            return;
        }
        
        console.log('[Car Filter] Поле автомобиля найдено:', carField);
        
        // 3. Устанавливаем начальный фильтр (если контакт уже выбран)
        const initialContactId = getCurrentContactId();
        if (initialContactId) {
            console.log('[Car Filter] Начальный контакт:', initialContactId);
            updateCarFilter(initialContactId, carField);
        }
        
        // 4. Отслеживаем изменения контакта
        setupContactObserver(contactField, carField);
        
        // 5. Также отслеживаем через события Битрикс
        setupBitrixEvents(carField);
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
    
    // Находит поле автомобиля (tile selector)
    function findCarField() {
        // Ищем по ID, который содержит код поля
        const fieldIdPattern = new RegExp(config.carFieldCode, 'i');
        const carField = document.querySelector(`[id*="${config.carFieldCode}"]`);
        
        if (!carField) {
            // Альтернативный поиск
            return document.querySelector('[data-field="' + config.carFieldCode + '"]') ||
                   document.querySelector('[name="' + config.carFieldCode + '"]');
        }
        
        return carField;
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
    
    // Настраивает отслеживание изменений контакта
    function setupContactObserver(contactField, carField) {
        // Используем MutationObserver для отслеживания изменений DOM
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' || mutation.type === 'subtree') {
                    const contactId = getCurrentContactId();
                    if (contactId) {
                        console.log('[Car Filter] Контакт изменен:', contactId);
                        updateCarFilter(contactId, carField);
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
    function setupBitrixEvents(carField) {
        if (typeof BX === 'undefined') return;
        
        // Событие изменения поля в редакторе
        BX.addCustomEvent('onCrmEntityEditorFieldValueChanged', function(event) {
            if (event.fieldName === 'CLIENT' || event.fieldName === 'CONTACT_ID') {
                const contactId = extractContactIdFromEvent(event);
                if (contactId) {
                    console.log('[Car Filter] Событие Битрикс: контакт изменен', contactId);
                    updateCarFilter(contactId, carField);
                }
            }
        });
        
        // Событие загрузки редактора
        BX.addCustomEvent('onCrmEntityEditorCreate', function(editor) {
            console.log('[Car Filter] Редактор создан');
            // Можно получить контакт из редактора
            setTimeout(function() {
                const contactId = getCurrentContactId();
                if (contactId) {
                    updateCarFilter(contactId, carField);
                }
            }, 500);
        });
    }
    
    // Извлекает ID контакта из события Битрикс
    function extractContactIdFromEvent(event) {
        if (!event.value) return null;
        
        if (typeof event.value === 'string') {
            // Формат "C_123" или просто "123"
            const match = event.value.match(/C_(\d+)|(\d+)/);
            return match ? (match[1] || match[2]) : null;
        }
        
        // Если значение - объект
        if (event.value.CONTACT_ID) {
            return event.value.CONTACT_ID;
        }
        
        return null;
    }
    
    // ОСНОВНАЯ ФУНКЦИЯ: Обновляет фильтр в поле автомобиля
    function updateCarFilter(contactId, carField) {
        console.log('[Car Filter] Обновление фильтра для контакта:', contactId);
        
        // Находим tile selector
        const tileSelector = getTileSelector(carField);
        if (!tileSelector) {
            console.log('[Car Filter] Tile selector не найден');
            return;
        }
        
        // Создаем фильтр
        const filter = {
            dynamic_1054: {
                filter: {
                    'CONTACT_ID': contactId
                },
                searchable: 'title'
            }
        };
        
        // Применяем фильтр
        applyFilterToTileSelector(tileSelector, filter);
        
        // Проверяем текущее значение (сбросим, если авто не принадлежит контакту)
        checkAndResetCurrentValue(carField, contactId);
    }
    
    // Получает объект tile selector
    function getTileSelector(carField) {
        // Пытаемся получить из BX
        if (typeof BX !== 'undefined' && BX.UI && BX.UI.TileSelectorManager) {
            const selectorId = carField.id;
            const tileSelector = BX.UI.TileSelectorManager.getById(selectorId);
            if (tileSelector) return tileSelector;
        }
        
        // Пытаемся получить из DOM
        if (carField._instance) {
            return carField._instance;
        }
        
        return null;
    }
    
    // Применяет фильтр к tile selector
    function applyFilterToTileSelector(tileSelector, filter) {
        if (!tileSelector || !filter) return;
        
        // Способ 1: через настройки поиска
        if (tileSelector._searchOptions) {
            tileSelector._searchOptions = Object.assign({}, tileSelector._searchOptions, filter);
            console.log('[Car Filter] Фильтр применен через _searchOptions');
        }
        
        // Способ 2: через настройки сущностей
        if (tileSelector._entityOptions) {
            tileSelector._entityOptions = Object.assign({}, tileSelector._entityOptions, filter);
            console.log('[Car Filter] Фильтр применен через _entityOptions');
        }
        
        // Способ 3: через параметры
        if (tileSelector._params) {
            tileSelector._params.filter = tileSelector._params.filter || {};
            Object.assign(tileSelector._params.filter, filter.dynamic_1054.filter);
            console.log('[Car Filter] Фильтр применен через _params');
        }
        
        // Очищаем текущий выбор
        if (typeof tileSelector.clear === 'function') {
            tileSelector.clear();
        }
    }
    
    // Проверяет и сбрасывает текущее значение, если оно не подходит
    function checkAndResetCurrentValue(carField, contactId) {
        // Находим скрытый input с значением
        const hiddenInput = carField.querySelector('input[type="hidden"]');
        if (!hiddenInput || !hiddenInput.value) return;
        
        const carId = hiddenInput.value;
        
        // Проверяем через API, принадлежит ли авто контакту
        if (typeof BX !== 'undefined' && BX.ajax) {
            BX.ajax.runAction('crm.api.entity.get', {
                data: {
                    entityTypeId: config.garageEntityId,
                    id: carId
                }
            }).then(function(response) {
                if (response.data && response.data.CONTACT_ID != contactId) {
                    // Авто принадлежит другому контакту - сбрасываем
                    hiddenInput.value = '';
                    console.log('[Car Filter] Значение сброшено: авто не принадлежит контакту');
                }
            }).catch(function(error) {
                console.error('[Car Filter] Ошибка проверки авто:', error);
            });
        }
    }
    
    // Запускаем инициализацию при загрузке страницы
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initCarFilter, 1500); // Ждем загрузки интерфейса
        });
    } else {
        setTimeout(initCarFilter, 1500);
    }
    
    // Экспортируем функции для отладки
    window.DEAL_CAR_FILTER = {
        init: initCarFilter,
        getCurrentContactId: getCurrentContactId,
        updateFilter: updateCarFilter
    };
    
})();