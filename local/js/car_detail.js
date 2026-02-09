// Локализация
var CarMessages = {
    'CAR_LOADING': 'Загрузка истории автомобиля...',
    'CAR_ERROR_TITLE': 'Ошибка загрузки',
    'CAR_ERROR_MESSAGE': 'Не удалось загрузить информацию об автомобиле',
    'CAR_POPUP_TITLE': 'История обслуживания автомобиля',
    'BTN_CLOSE': 'Закрыть',
    'BTN_OPEN_CARD': 'Открыть карточку авто',
    'BTN_HISTORY': 'История'
};

function getMessage(key) {
    return (typeof BX !== 'undefined' && BX.message && BX.message[key]) 
        ? BX.message[key] 
        : CarMessages[key] || key;
}

// ========== ОСНОВНАЯ ФУНКЦИЯ ПОКАЗА ПОПАПА ==========
function showCarDetail(carId) {
    if (!BX) {
        alert('Библиотека BX не загружена');
        return;
    }
    
    // Показываем уведомление
    if (BX.UI && BX.UI.Notification) {
        BX.UI.Notification.Center.notify({
            content: getMessage('CAR_LOADING'),
            autoHideDelay: 2000
        });
    }
    
    // AJAX запрос
    BX.ajax({
        url: '/local/components/custom/car.detail/ajax.php',
        data: {
            car_id: carId,
            sessid: BX.bitrix_sessid()
        },
        method: 'POST',
        dataType: 'html',
        onsuccess: function(html) {
            // Создаем попап
            var popup = new BX.PopupWindow('car-history-' + carId, null, {
                content: html,
                width: 900,
                height: 650,
                closeIcon: true,
                title: getMessage('CAR_POPUP_TITLE'),
                buttons: [
                    new BX.PopupWindowButton({
                        text: getMessage('BTN_CLOSE'),
                        className: 'ui-btn ui-btn-primary',
                        events: { 
                            click: function() { 
                                popup.close(); 
                            } 
                        }
                    })
                ]
            });
            
            // Добавляем вторую кнопку, если есть SidePanel
            if (BX.SidePanel && BX.SidePanel.Instance) {
                popup.addButton(new BX.PopupWindowButton({
                    text: getMessage('BTN_OPEN_CARD'),
                    className: 'ui-btn ui-btn-light-border',
                    events: {
                        click: function() {
                            BX.SidePanel.Instance.open('/crm/type/1054/details/' + carId + '/', {
                                cacheable: false,
                                width: 900
                            });
                            popup.close();
                        }
                    }
                }));
            }
            
            popup.show();
        },
        onfailure: function() {
            if (BX.UI && BX.UI.Dialogs && BX.UI.Dialogs.MessageBox) {
                BX.UI.Dialogs.MessageBox.alert(
                    getMessage('CAR_ERROR_TITLE'),
                    getMessage('CAR_ERROR_MESSAGE')
                );
            } else {
                alert(getMessage('CAR_ERROR_MESSAGE'));
            }
        }
    });
}

// ========== ФУНКЦИЯ ДОБАВЛЕНИЯ КНОПОК В ТАБЛИЦУ ==========
function addHistoryButtonsToGarage() {
    // Ищем таблицу по ID из вашего HTML
    var table = document.querySelector('#crm-type-item-list-1054-10parent_3_table');
    
    if (!table) {
        console.log('Таблица гаража не найдена по ID');
        return;
    }
    
    // Ищем строки с данными (не заголовок и не шаблон)
    var rows = table.querySelectorAll('tbody tr:not([hidden]):not([data-id^="template"])');
    
    if (rows.length === 0) {
        console.log('Не найдено строк с данными в таблице');
        return;
    }
    
    console.log('Найдено строк:', rows.length);
    
    // Для каждой строки добавляем кнопку
    rows.forEach(function(row, index) {
        // Проверяем, не добавили ли уже кнопку
        if (row.querySelector('.car-history-button')) {
            return;
        }
        
        // Получаем ID автомобиля из строки
        var carId = extractCarIdFromRow(row);
        if (!carId) {
            console.log('Не удалось извлечь ID автомобиля из строки', index);
            return;
        }
        
        console.log('Обработка автомобиля ID:', carId);
        
        // Находим ячейку с ссылкой на авто (столбец "Название")
        var titleCell = row.querySelector('td[data-column-id="TITLE"]');
        if (!titleCell) {
            console.log('Не найден столбец "Название"');
            return;
        }
        
        // Создаем кнопку
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'ui-btn ui-btn-light-border car-history-button';
        button.innerHTML = '<span class="ui-btn-text">' + getMessage('BTN_HISTORY') + '</span>';
        button.style.cssText = 'margin-left: 10px; font-size: 12px; padding: 4px 10px;';
        button.dataset.carId = carId;
        
        // Обработчик клика
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Клик по кнопке для авто ID:', carId);
            showCarDetail(carId);
        });
        
        // Добавляем кнопку после ссылки в ячейке
        var link = titleCell.querySelector('a[href*="/crm/type/1054/details/"]');
        if (link) {
            link.parentNode.insertBefore(button, link.nextSibling);
        } else {
            // Если ссылки нет, добавляем в конец ячейки
            titleCell.appendChild(button);
        }
        
        console.log('Кнопка добавлена для авто ID:', carId);
    });
    
    console.log('Добавлено кнопок:', rows.length);
}

// Функция извлечения ID автомобиля из строки таблицы
function extractCarIdFromRow(row) {
    // Способ 1: Из атрибута data-id строки
    if (row.dataset && row.dataset.id && row.dataset.id !== 'template_0') {
        return row.dataset.id;
    }
    
    // Способ 2: Из ссылки в столбце "Название"
    var link = row.querySelector('a[href*="/crm/type/1054/details/"]');
    if (link && link.href) {
        var match = link.href.match(/\/details\/(\d+)/);
        if (match && match[1]) {
            return match[1];
        }
    }
    
    // Способ 3: Из текста ссылки (если в href нет ID)
    if (link && link.textContent) {
        var textMatch = link.textContent.match(/\((\d+)\)/);
        if (textMatch && textMatch[1]) {
            return textMatch[1];
        }
    }
    
    return null;
}

// ========== СЛУШАТЕЛИ СОБЫТИЙ ДЛЯ ДИНАМИЧЕСКОЙ ТАБЛИЦЫ ==========
function waitForTableAndAddButtons() {
    console.log('Ожидание загрузки таблицы гаража...');
    
    // Вариант 1: Ожидание появления таблицы
    var checkTableInterval = setInterval(function() {
        var table = document.querySelector('#crm-type-item-list-1054-10parent_3_table');
        if (table) {
            console.log('Таблица найдена, добавляем кнопки...');
            addHistoryButtonsToGarage();
            clearInterval(checkTableInterval);
            
            // После добавления, следим за изменениями таблицы
            observeTableChanges(table);
        }
    }, 500);
    
    // Останавливаем проверку через 30 секунд
    setTimeout(function() {
        clearInterval(checkTableInterval);
    }, 30000);
    
    // Вариант 2: Слушаем событие загрузки данных
    if (BX && BX.Event) {
        BX.Event.EventEmitter.subscribe('BX.Crm.ItemListComponent:onDataLoaded', function(event) {
            console.log('Событие загрузки данных таблицы');
            setTimeout(addHistoryButtonsToGarage, 1000);
        });
    }
}

// Наблюдатель за изменениями таблицы (если данные подгружаются динамически)
function observeTableChanges(table) {
    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    // Проверяем, появились ли новые строки
                    var newRows = table.querySelectorAll('tbody tr:not([hidden]):not([data-id^="template"]):not(.has-history-button)');
                    if (newRows.length > 0) {
                        console.log('Обнаружены новые строки:', newRows.length);
                        addHistoryButtonsToGarage();
                    }
                }
            });
        });
        
        observer.observe(table.querySelector('tbody'), {
            childList: true,
            subtree: true
        });
        
        console.log('Наблюдатель за таблицей запущен');
    }
}

// ========== ИНИЦИАЛИЗАЦИЯ ==========
// Ждем загрузки DOM и Битрикс
if (typeof BX !== 'undefined') {
    BX.ready(function() {
        console.log('BX.ready - запуск добавления кнопок');
        
        // Ждем немного, чтобы таблица успела отрисоваться
        setTimeout(function() {
            waitForTableAndAddButtons();
        }, 1500);
        
        // Также запускаем при изменении вкладок (если Гараж не сразу активен)
        if (BX.addCustomEvent) {
            BX.addCustomEvent(window, 'SidePanel.Slider:onMessage', function(event) {
                if (event.data.event === 'BX.Crm.EntityEditor:onTabActivated') {
                    console.log('Активирована новая вкладка');
                    setTimeout(waitForTableAndAddButtons, 1000);
                }
            });
        }
    });
} else {
    // Если BX еще не загружен, ждем
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(waitForTableAndAddButtons, 2000);
    });
}

// Альтернативный быстрый способ для тестирования - просто добавить кнопки через 3 секунды
setTimeout(function() {
    console.log('Запуск отложенного добавления кнопок');
    addHistoryButtonsToGarage();
}, 3000);