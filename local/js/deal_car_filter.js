/**
 * Фильтрация автомобилей по выбранному контакту в сделке
 * УПРОЩЕННАЯ РАБОЧАЯ ВЕРСИЯ
 */

(function() {
    'use strict';
    
    // Конфигурация
    const SMART_PROCESS_TYPE_ID = 1054;
    const ENTITY_CODE = 'DYNAMICS_' + SMART_PROCESS_TYPE_ID;
    const CAR_FIELD_ID = 'UF_CRM_1770588718';
    
    // Состояние
    let currentContactId = null;
    let isInitialized = false;
    
    console.log('DealCarFilter: Загрузка упрощенной версии');
    
    // ==================== ОСНОВНЫЕ ФУНКЦИИ ====================
    
    /**
     * Найти ID контакта в DOM
     */
    function findContactIdInDOM() {
        // Ищем секцию контакта по data-cid
        const contactElements = document.querySelectorAll('[data-cid]');
        
        for (let element of contactElements) {
            const dataCid = element.getAttribute('data-cid');
            if (dataCid && dataCid.startsWith('CONTACT_')) {
                const match = dataCid.match(/CONTACT_(\d+)_/);
                if (match && match[1]) {
                    return match[1];
                }
            }
        }
        
        return null;
    }
    
    /**
     * Получить текущий контакт (из памяти или DOM)
     */
    function getCurrentContact() {
        return currentContactId;
    }
    
    /**
     * Сбросить селектор автомобилей
     */
    function resetCarSelector() {
        console.log('DealCarFilter: Сброс селектора');
        
        // 1. Очистить скрытое поле
        const carField = document.getElementById(CAR_FIELD_ID);
        if (carField) {
            carField.value = '';
        }
        
        // 2. Очистить видимые плитки
        const tileContainer = document.querySelector('.ui-tile-selector-selector-wrap');
        if (tileContainer) {
            const tiles = tileContainer.querySelectorAll('[data-role="tile-item"]');
            tiles.forEach(tile => tile.remove());
        }
        
        // 3. Найти и пересоздать кнопку "выбрать"
        recreateSelectButton();
        
        // 4. Очистить кеш селектора
        clearSelectorCache();
        
        console.log('DealCarFilter: Селектор сброшен');
    }
    
    /**
     * Пересоздать кнопку "выбрать" с новым обработчиком
     */
    function recreateSelectButton() {
        const selectButton = document.querySelector('[data-role="tile-select"]');
        if (!selectButton) {
            console.log('DealCarFilter: Кнопка выбора не найдена');
            return;
        }
        
        // Клонируем кнопку
        const newButton = selectButton.cloneNode(true);
        const parent = selectButton.parentNode;
        
        // Заменяем старую кнопку
        parent.replaceChild(newButton, selectButton);
        
        // Вешаем новый обработчик
        newButton.addEventListener('click', function(event) {
            console.log('DealCarFilter: Клик на кнопку "выбрать"');
            console.log('DealCarFilter: Текущий контакт:', currentContactId);
            
            // Предотвращаем стандартное поведение
            event.preventDefault();
            event.stopPropagation();
            
            // Запускаем загрузку данных
            loadCarData();
        });
        
        console.log('DealCarFilter: Кнопка пересоздана');
    }
    
    /**
     * Загрузить данные автомобилей
     */
    function loadCarData() {
        console.log('DealCarFilter: Загрузка данных автомобилей');
        
        // Создаем параметры запроса
        const params = new URLSearchParams();
        
        // Базовые параметры
        params.append('mode', 'ajax');
        params.append('c', 'bitrix:main.ui.selector');
        params.append('action', 'getData');
        
        // Опции
        params.append('data[options][useNewCallback]', 'Y');
        params.append('data[options][context]', 'crmEntityCreate');
        params.append('data[options][enableCrm]', 'Y');
        params.append('data[options][crmPrefixType]', 'SHORT');
        params.append('data[options][enableCrmDynamics][1054]', 'Y');
        params.append('data[options][multiple]', 'N');
        
        // Entity Types
        params.append('data[entityTypes][DYNAMICS_1054][options][typeId]', '1054');
        params.append('data[entityTypes][DYNAMICS_1054][options][enableSearch]', 'Y');
        params.append('data[entityTypes][DYNAMICS_1054][options][searchById]', 'Y');
        params.append('data[entityTypes][DYNAMICS_1054][options][prefixType]', 'SHORT');
        params.append('data[entityTypes][DYNAMICS_1054][options][returnItemUrl]', 'Y');
        params.append('data[entityTypes][DYNAMICS_1054][options][title]', 'Гараж');
        
        // ДОБАВЛЯЕМ ФИЛЬТР ПО КОНТАКТУ
        if (currentContactId) {
            params.append(`data[FILTER][${ENTITY_CODE}][=CONTACT_ID]`, currentContactId);
            console.log('DealCarFilter: Добавлен фильтр для контакта', currentContactId);
        }
        
        // Отправляем запрос
        sendSelectorRequest(params);
    }
    
    /**
     * Отправить запрос к селектору
     */
    function sendSelectorRequest(params) {
        const url = '/bitrix/services/main/ajax.php?' + params.toString();
        console.log('DealCarFilter: Отправка запроса:', url.substring(0, 150) + '...');
        
        // Используем fetch для отправки
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('DealCarFilter: Получен ответ от сервера');
            
            // Обрабатываем данные
            if (data && data.data && data.data.ENTITIES && data.data.ENTITIES[ENTITY_CODE]) {
                processCarData(data.data.ENTITIES[ENTITY_CODE]);
            } else {
                console.warn('DealCarFilter: Нет данных об автомобилях в ответе');
            }
        })
        .catch(error => {
            console.error('DealCarFilter: Ошибка при запросе:', error);
        });
    }
    
    /**
     * Обработать полученные данные об автомобилях
     */
    function processCarData(carData) {
        console.log('DealCarFilter: Обработка данных автомобилей');
        console.log('DealCarFilter: Найдено автомобилей:', Object.keys(carData.ITEMS || {}).length);
        
        // Здесь можно отобразить данные в попапе
        // Пока просто логируем
        if (carData.ITEMS) {
            Object.values(carData.ITEMS).forEach(car => {
                console.log('Автомобиль:', car.id, '-', car.name);
            });
        }
        
        // Открываем попап с данными
        openCarSelectorPopup(carData);
    }
    
    /**
     * Открыть попап выбора автомобилей
     */
    function openCarSelectorPopup(carData) {
        console.log('DealCarFilter: Открытие попапа выбора');
        
        // Создаем простой попап для выбора
        const popupHtml = `
            <div id="deal-car-filter-popup" style="
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                border: 1px solid #ccc;
                padding: 20px;
                z-index: 10000;
                box-shadow: 0 0 10px rgba(0,0,0,0.3);
                max-height: 400px;
                overflow-y: auto;
                min-width: 300px;
            ">
                <h3 style="margin-top: 0;">Выберите автомобиль</h3>
                <div id="car-list"></div>
                <button onclick="document.getElementById('deal-car-filter-popup').remove()" 
                        style="margin-top: 10px; padding: 5px 10px;">
                    Закрыть
                </button>
            </div>
        `;
        
        // Добавляем попап на страницу
        const popupContainer = document.createElement('div');
        popupContainer.innerHTML = popupHtml;
        document.body.appendChild(popupContainer.firstElementChild);
        
        // Заполняем список автомобилей
        const carList = document.getElementById('car-list');
        if (carData.ITEMS) {
            Object.values(carData.ITEMS).forEach(car => {
                const carItem = document.createElement('div');
                carItem.style.padding = '5px';
                carItem.style.borderBottom = '1px solid #eee';
                carItem.innerHTML = `
                    <input type="radio" name="selected-car" value="${car.entityId}" 
                           id="car-${car.entityId}">
                    <label for="car-${car.entityId}" style="margin-left: 5px; cursor: pointer;">
                        ${car.name}
                    </label>
                `;
                
                // Обработчик выбора
                carItem.querySelector('input').addEventListener('change', function() {
                    if (this.checked) {
                        selectCar(car.entityId, car.name);
                    }
                });
                
                carList.appendChild(carItem);
            });
        } else {
            carList.innerHTML = '<p>Нет автомобилей для выбранного контакта</p>';
        }
    }
    
    /**
     * Выбрать автомобиль
     */
    function selectCar(carId, carName) {
        console.log('DealCarFilter: Выбран автомобиль:', carId, carName);
        
        // 1. Заполняем скрытое поле
        const carField = document.getElementById(CAR_FIELD_ID);
        if (carField) {
            carField.value = carId;
        }
        
        // 2. Обновляем видимое поле
        const tileContainer = document.querySelector('[data-role="tile-container"]');
        if (tileContainer) {
            // Очищаем старые плитки
            const oldTiles = tileContainer.querySelectorAll('[data-role="tile-item"]');
            oldTiles.forEach(tile => tile.remove());
            
            // Добавляем новую плитку
            const tileHtml = `
                <span data-role="tile-item" data-bx-id="D${carId}" 
                      class="ui-tile-selector-item ui-tile-selector-item-dynamic_1054">
                    <span data-role="tile-item-name">${carName}</span>
                    <span data-role="remove" class="ui-tile-selector-item-remove"></span>
                </span>
            `;
            
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = tileHtml;
            tileContainer.insertBefore(tempDiv.firstElementChild, tileContainer.firstChild);
        }
        
        // 3. Закрываем попап
        const popup = document.getElementById('deal-car-filter-popup');
        if (popup) {
            popup.remove();
        }
        
        console.log('DealCarFilter: Автомобиль выбран и отображен');
    }
    
    /**
     * Очистить кеш селектора
     */
    function clearSelectorCache() {
        if (window.BX && BX.Main && BX.Main.SelectorManager && BX.Main.SelectorManager.DataStore) {
            Object.keys(BX.Main.SelectorManager.DataStore).forEach(key => {
                if (key.includes('DYNAMICS_1054') || key.includes(CAR_FIELD_ID)) {
                    delete BX.Main.SelectorManager.DataStore[key];
                }
            });
        }
    }
    
    /**
     * Отслеживать выбор контакта
     */
    function watchForContactSelection() {
        const observer = new MutationObserver(function(mutations) {
            for (let mutation of mutations) {
                // Проверяем изменения в data-cid
                if (mutation.type === 'attributes' && mutation.attributeName === 'data-cid') {
                    const dataCid = mutation.target.getAttribute('data-cid');
                    if (dataCid && dataCid.startsWith('CONTACT_')) {
                        handleContactChange();
                        break;
                    }
                }
                
                // Проверяем добавление элементов
                if (mutation.type === 'childList') {
                    for (let node of mutation.addedNodes) {
                        if (node.nodeType === 1 && node.hasAttribute && 
                            node.getAttribute('data-cid') && node.getAttribute('data-cid').startsWith('CONTACT_')) {
                            handleContactChange();
                            break;
                        }
                    }
                }
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['data-cid']
        });
        
        console.log('DealCarFilter: Наблюдение за контактом запущено');
    }
    
    /**
     * Обработать изменение контакта
     */
    function handleContactChange() {
        setTimeout(() => {
            const newContactId = findContactIdInDOM();
            
            if (newContactId && newContactId !== currentContactId) {
                currentContactId = newContactId;
                console.log('DealCarFilter: Контакт изменен, ID:', currentContactId);
                
                // Сбрасываем селектор автомобилей
                resetCarSelector();
            }
        }, 100);
    }
    
    /**
     * Инициализировать модуль
     */
    function initialize() {
        if (isInitialized) return;
        
        console.log('DealCarFilter: Инициализация модуля');
        
        // 1. Начинаем наблюдать за выбором контакта
        watchForContactSelection();
        
        // 2. Проверяем, может контакт уже выбран
        setTimeout(() => {
            const existingContact = findContactIdInDOM();
            if (existingContact) {
                currentContactId = existingContact;
                console.log('DealCarFilter: Контакт уже выбран при загрузке:', currentContactId);
            }
        }, 1000);
        
        // 3. Пересоздаем кнопку выбора
        setTimeout(() => {
            recreateSelectButton();
        }, 1500);
        
        isInitialized = true;
        console.log('DealCarFilter: Модуль инициализирован');
    }
    
    // ==================== ПУБЛИЧНЫЕ МЕТОДЫ ====================
    
    window.DealCarFilter = {
        /**
         * Получить текущий контакт
         */
        getContact: function() {
            return getCurrentContact();
        },
        
        /**
         * Принудительно сбросить селектор
         */
        reset: function() {
            resetCarSelector();
            console.log('DealCarFilter: Принудительный сброс');
        },
        
        /**
         * Загрузить автомобили вручную
         */
        loadCars: function() {
            if (!currentContactId) {
                console.warn('DealCarFilter: Контакт не выбран');
                return;
            }
            loadCarData();
        },
        
        /**
         * Отладочная информация
         */
        debug: function() {
            console.log('=== DealCarFilter Debug ===');
            console.log('Текущий контакт:', currentContactId);
            console.log('Контакт в DOM:', findContactIdInDOM());
            console.log('Инициализирован:', isInitialized);
            
            const selectBtn = document.querySelector('[data-role="tile-select"]');
            console.log('Кнопка выбора:', selectBtn ? 'найдена' : 'не найдена');
            
            const carField = document.getElementById(CAR_FIELD_ID);
            console.log('Поле автомобиля:', carField ? 'найдено' : 'не найдено');
            console.log('Значение поля:', carField ? carField.value : 'N/A');
            
            return {
                contact: currentContactId,
                contactInDOM: findContactIdInDOM(),
                initialized: isInitialized,
                hasButton: !!selectBtn
            };
        },
        
        /**
         * Тестовый запрос
         */
        testRequest: function() {
            console.log('DealCarFilter: Тестовый запрос');
            loadCarData();
        }
    };
    
    // ==================== ЗАПУСК ====================
    
    // Ждем загрузки DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initialize, 500);
        });
    } else {
        setTimeout(initialize, 500);
    }
    
    // Дополнительный запуск через 3 секунды
    setTimeout(() => {
        if (!isInitialized) {
            console.log('DealCarFilter: Дополнительный запуск');
            initialize();
        }
    }, 3000);
    
})();