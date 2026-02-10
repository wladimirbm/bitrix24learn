/**
 * Фильтрация автомобилей по выбранному контакту в сделке
 * ВЕРСИЯ С ПРОВЕРКОЙ КОНТАКТА
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
    let originalButtonHandler = null;
    
    console.log('DealCarFilter: Загрузка версии с проверкой контакта');
    
    // ==================== ОСНОВНЫЕ ФУНКЦИИ ====================
    
    /**
     * Найти ID контакта в DOM
     */
    function findContactIdInDOM() {
        // Ищем секцию контакта по data-cid
        const contactSection = document.querySelector('[data-cid^="CONTACT_"]');
        
        if (contactSection) {
            const dataCid = contactSection.getAttribute('data-cid');
            const match = dataCid.match(/CONTACT_(\d+)_/);
            if (match && match[1]) {
                return match[1];
            }
        }
        
        return null;
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
        
        console.log('DealCarFilter: Селектор сброшен');
    }
    
    /**
     * Пересоздать кнопку "выбрать" с проверкой контакта
     */
    function recreateSelectButton() {
        const selectButton = document.querySelector('[data-role="tile-select"]');
        if (!selectButton) {
            console.log('DealCarFilter: Кнопка выбора не найдена');
            return;
        }
        
        // Сохраняем оригинальную кнопку для восстановления
        const originalButton = selectButton.cloneNode(true);
        
        // Клонируем кнопку
        const newButton = selectButton.cloneNode(true);
        const parent = selectButton.parentNode;
        
        // Заменяем старую кнопку
        parent.replaceChild(newButton, selectButton);
        
        // Вешаем новый обработчик С ПРОВЕРКОЙ
        newButton.addEventListener('click', function handleButtonClick(event) {
            console.log('DealCarFilter: Клик на кнопку "выбрать"');
            console.log('DealCarFilter: Текущий контакт:', currentContactId);
            
            // ВАЖНО: Если контакт НЕ выбран - ВОССТАНАВЛИВАЕМ СТАНДАРТНОЕ ПОВЕДЕНИЕ
            if (!currentContactId) {
                console.log('DealCarFilter: Контакт не выбран! Используем стандартный селектор');
                
                // Удаляем наш обработчик
                newButton.removeEventListener('click', handleButtonClick);
                
                // Восстанавливаем оригинальную кнопку
                const restoredButton = originalButton.cloneNode(true);
                parent.replaceChild(restoredButton, newButton);
                
                // Кликаем на восстановленную кнопку для стандартного поведения
                setTimeout(() => {
                    restoredButton.click();
                }, 10);
                
                return; // Прекращаем выполнение нашего кода
            }
            
            // Если контакт ВЫБРАН - выполняем нашу логику
            console.log('DealCarFilter: Контакт выбран, применяем фильтр');
            
            // Предотвращаем стандартное поведение
            event.preventDefault();
            event.stopPropagation();
            
            // Загружаем данные с фильтром
            loadCarData();
        });
        
        console.log('DealCarFilter: Кнопка пересоздана с проверкой контакта');
    }
    
    /**
     * Загрузить данные автомобилей (только при выбранном контакте)
     */
    function loadCarData() {
        // Двойная проверка
        if (!currentContactId) {
            console.warn('DealCarFilter: Попытка загрузки без выбранного контакта');
            return;
        }
        
        console.log('DealCarFilter: Загрузка данных автомобилей для контакта', currentContactId);
        
        // Получаем CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                         document.querySelector('input[name="sessid"]')?.value;
        
        if (!csrfToken) {
            console.error('DealCarFilter: CSRF token не найден');
            return;
        }
        
        // Создаем FormData
        const formData = new FormData();
        
        // Базовые параметры
        formData.append('mode', 'ajax');
        formData.append('c', 'bitrix:main.ui.selector');
        formData.append('action', 'getData');
        formData.append('sessid', csrfToken);
        
        // Опции (как в стандартном запросе)
        formData.append('data[options][useNewCallback]', 'Y');
        formData.append('data[options][context]', 'crmEntityCreate');
        formData.append('data[options][enableCrm]', 'Y');
        formData.append('data[options][crmPrefixType]', 'SHORT');
        formData.append('data[options][enableCrmDynamics][1054]', 'Y');
        formData.append('data[options][multiple]', 'N');
        
        // Entity Types
        formData.append('data[entityTypes][DYNAMICS_1054][options][typeId]', '1054');
        formData.append('data[entityTypes][DYNAMICS_1054][options][enableSearch]', 'Y');
        formData.append('data[entityTypes][DYNAMICS_1054][options][searchById]', 'Y');
        formData.append('data[entityTypes][DYNAMICS_1054][options][prefixType]', 'SHORT');
        formData.append('data[entityTypes][DYNAMICS_1054][options][returnItemUrl]', 'Y');
        formData.append('data[entityTypes][DYNAMICS_1054][options][title]', 'Гараж');
        
        // ВАЖНО: ДОБАВЛЯЕМ ФИЛЬТР ПО КОНТАКТУ
        formData.append(`data[FILTER][${ENTITY_CODE}][=CONTACT_ID]`, currentContactId);
        console.log('DealCarFilter: Добавлен фильтр для контакта', currentContactId);
        
        // Отправляем запрос
        sendSelectorRequest(formData);
    }
    
    /**
     * Отправить запрос к селектору
     */
    function sendSelectorRequest(formData) {
        console.log('DealCarFilter: Отправка запроса с фильтром');
        
        fetch('/bitrix/services/main/ajax.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('DealCarFilter: Ответ от сервера:', data);
            
            if (data.status === 'success' && data.data && data.data.ENTITIES && data.data.ENTITIES[ENTITY_CODE]) {
                processCarData(data.data.ENTITIES[ENTITY_CODE]);
            } else {
                console.warn('DealCarFilter: Нет данных в ответе или ошибка:', data.errors || 'неизвестная ошибка');
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
        
        if (carData.ITEMS) {
            Object.values(carData.ITEMS).forEach(car => {
                console.log('Автомобиль:', car.id, '-', car.name);
            });
            
            // Открываем попап с данными
            openCarSelectorPopup(carData);
        } else {
            console.warn('DealCarFilter: Нет автомобилей для выбранного контакта');
            alert('Нет автомобилей для выбранного контакта');
        }
    }
    
    /**
     * Открыть попап выбора автомобилей
     */
    function openCarSelectorPopup(carData) {
        console.log('DealCarFilter: Открытие попапа выбора');
        
        // Создаем простой попап
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
                <h3 style="margin-top: 0;">Выберите автомобиль (контакт: ${currentContactId})</h3>
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
     * Отслеживать выбор контакта
     */
    function watchForContactSelection() {
        const observer = new MutationObserver(function(mutations) {
            let contactChanged = false;
            
            for (let mutation of mutations) {
                // Изменение data-cid
                if (mutation.type === 'attributes' && 
                    mutation.attributeName === 'data-cid' &&
                    mutation.target.getAttribute('data-cid') &&
                    mutation.target.getAttribute('data-cid').startsWith('CONTACT_')) {
                    contactChanged = true;
                    break;
                }
                
                // Добавление узлов
                if (mutation.type === 'childList') {
                    for (let node of mutation.addedNodes) {
                        if (node.nodeType === 1) {
                            if (node.getAttribute && node.getAttribute('data-cid') && 
                                node.getAttribute('data-cid').startsWith('CONTACT_')) {
                                contactChanged = true;
                                break;
                            }
                        }
                    }
                }
            }
            
            if (contactChanged) {
                setTimeout(() => {
                    const newContactId = findContactIdInDOM();
                    if (newContactId && newContactId !== currentContactId) {
                        currentContactId = newContactId;
                        console.log('DealCarFilter: Контакт изменен! ID:', currentContactId);
                        
                        // Сбрасываем селектор
                        resetCarSelector();
                        
                        // Пересоздаем кнопку с новым контактом
                        recreateSelectButton();
                    }
                }, 200);
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
            return currentContactId;
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
                alert('Сначала выберите контакт');
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
            
            return {
                contact: currentContactId,
                contactInDOM: findContactIdInDOM(),
                initialized: isInitialized
            };
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