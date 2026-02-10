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
                return;
            }
            
            // 2. Начинаем отслеживание изменений в блоке клиента
            startObservingClientBlock(clientBlock);
            
            // 3. Перехватываем запросы селектора "Автомобиль"
            interceptSelectorRequests();
            
            console.log('DealCarFilter: Модуль инициализирован');
        });
    }
    
    /**
     * Отслеживание изменений в блоке "Клиент" для определения выбранного контакта
     */
    function startObservingClientBlock(clientBlock) {
        const observer = new MutationObserver(function(mutations) {
            // Ищем значок контакта (иконка + бейдж)
            const contactIcon = clientBlock.querySelector('.crm-entity-widget-img-contact');
            if (!contactIcon) return;
            
            // Бейдж находится сразу после иконки контакта
            const contactBadge = contactIcon.nextElementSibling;
            
            if (contactBadge && contactBadge.classList.contains('crm-entity-widget-badge')) {
                const entityId = contactBadge.getAttribute('data-entity-id');
                
                if (entityId && entityId !== currentContactId) {
                    // Контакт выбран или изменен
                    currentContactId = entityId;
                    console.log('DealCarFilter: Выбран контакт ID:', currentContactId);
                } else if (!entityId && currentContactId) {
                    // Контакт сброшен
                    currentContactId = null;
                    console.log('DealCarFilter: Контакт сброшен');
                }
            }
        });
        
        // Начинаем наблюдение за изменениями в блоке клиента
        observer.observe(clientBlock, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['data-entity-id', 'class']
        });
    }
    
    /**
     * Перехват запросов селектора "Автомобиль"
     */
    function interceptSelectorRequests() {
        // Событие вызывается перед загрузкой данных в любом селекторе
        BX.addCustomEvent('UI::Selector::Item:onBeforeLoad', function(control, params) {
            // Проверяем, что это селектор для нашего поля "Автомобиль"
            // Поле имеет ID UF_CRM_1770588718 (из вашего HTML)
            if (!isCarSelector(control)) {
                return;
            }
            
            // Если контакт выбран - добавляем фильтр
            if (currentContactId) {
                // Убедимся, что существует объект FILTER
                if (!params.data.FILTER) {
                    params.data.FILTER = {};
                }
                
                // Добавляем фильтр по CONTACT_ID для смарт-процесса
                params.data.FILTER[ENTITY_CODE] = {
                    '=CONTACT_ID': currentContactId
                };
                
                console.log('DealCarFilter: Добавлен фильтр для контакта', currentContactId);
            } else {
                // Если контакт не выбран - убедимся, что фильтр не применяется
                if (params.data.FILTER && params.data.FILTER[ENTITY_CODE]) {
                    delete params.data.FILTER[ENTITY_CODE];
                    console.log('DealCarFilter: Фильтр сброшен (контакт не выбран)');
                }
            }
        });
    }
    
    /**
     * Проверка, что это селектор поля "Автомобиль"
     */
    function isCarSelector(control) {
        // Проверяем различными способами:
        
        // 1. По ID контрола (может содержать UF_CRM_1770588718)
        if (control.id && control.id.indexOf('UF_CRM_1770588718') !== -1) {
            return true;
        }
        
        // 2. По ID скрытого поля в DOM
        const hiddenInput = document.getElementById('UF_CRM_1770588718');
        if (hiddenInput && control.container) {
            const containerId = control.container.id || '';
            if (containerId.indexOf(hiddenInput.id) !== -1) {
                return true;
            }
        }
        
        // 3. По наличию определенных классов или атрибутов
        if (control.container) {
            const container = control.container;
            // Ищем текст "Автомобиль" в заголовках рядом
            const surroundingText = container.parentElement ? container.parentElement.textContent : '';
            if (surroundingText.indexOf('Автомобиль') !== -1) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Публичные методы (можно вызывать из консоли для отладки)
     */
    window.DealCarFilter = {
        /**
         * Получить ID текущего выбранного контакта
         */
        getCurrentContact: function() {
            return currentContactId;
        },
        
        /**
         * Принудительно установить контакт (для отладки)
         */
        setContactForDebug: function(contactId) {
            currentContactId = contactId;
            console.log('DealCarFilter: Установлен контакт для отладки:', contactId);
        },
        
        /**
         * Сбросить выбранный контакт
         */
        resetContact: function() {
            currentContactId = null;
            console.log('DealCarFilter: Контакт сброшен вручную');
        },
        
        /**
         * Проверить состояние модуля
         */
        getStatus: function() {
            return {
                initialized: true,
                currentContactId: currentContactId,
                smartProcessType: SMART_PROCESS_TYPE_ID,
                entityCode: ENTITY_CODE
            };
        }
    };
    
    // Инициализируем модуль при загрузке
    init();
})();