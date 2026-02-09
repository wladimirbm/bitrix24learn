(function() {
    console.log('🚗 Модуль истории авто загружается...');
    
    // УБИРАЕМ ВСЕ КНОПКИ СРАЗУ
    function removeAllHistoryButtons() {
        var buttons = document.querySelectorAll('.car-history-button, .car-history-btn, button[data-car-id]');
        buttons.forEach(function(btn) {
            btn.style.display = 'none';
            btn.remove();
        });
        console.log('🗑️ Удалены кнопки:', buttons.length);
    }
    
    // ОСНОВНАЯ ФУНКЦИЯ ПОПАПА
    window.showCarHistory = function(carId, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        console.log('📱 Открытие истории авто ID:', carId);
        
        if (typeof BX === 'undefined') {
            window.open('/crm/type/1054/details/' + carId + '/', '_blank');
            return;
        }
        
        // Показываем лоадер
        BX.showWait();
        
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
                BX.closeWait();
                console.log('✅ AJAX успешен');
                
                var popup = new BX.PopupWindow('car-history-' + carId, null, {
                    content: html,
                    width: 900,
                    height: 650,
                    closeIcon: true,
                    title: 'История автомобиля',
                    buttons: [
                        new BX.PopupWindowButton({
                            text: 'Закрыть',
                            className: 'ui-btn ui-btn-primary',
                            events: {
                                click: function() {
                                    popup.close();
                                }
                            }
                        })
                    ]
                });
                
                popup.show();
                console.log('✅ Попап показан');
            },
            onfailure: function() {
                BX.closeWait();
                console.error('❌ AJAX ошибка');
                BX.UI.Dialogs.MessageBox.alert('Ошибка', 'Не удалось загрузить информацию');
            }
        });
    };
    
    // ПЕРЕХВАТЫВАЕМ ССЫЛКИ НА АВТО
    function interceptCarLinks() {
        console.log('🔍 Поиск ссылок на авто...');
        
        // Находим все ссылки на автомобили в таблице
        var links = document.querySelectorAll('a[href*="/crm/type/1054/details/"]');
        console.log('🔗 Найдено ссылок:', links.length);
        
        links.forEach(function(link) {
            // Получаем ID авто из ссылки
            var href = link.getAttribute('href');
            var match = href.match(/\/details\/(\d+)/);
            if (!match) return;
            
            var carId = match[1];
            
            // Заменяем обработчик клика
            link.onclick = function(e) {
                // Разрешаем открытие в новой вкладке
                if (e.shiftKey || e.ctrlKey || e.metaKey) {
                    return true;
                }
                
                // Открываем наш попап
                e.preventDefault();
                e.stopImmediatePropagation();
                
                console.log('🖱️ Клик по названию авто ID:', carId);
                showCarHistory(carId, e);
                
                return false;
            };
            
            // Добавляем курсор-указатель
            link.style.cursor = 'pointer';
            link.title = 'Клик - показать историю\nShift+клик - открыть карточку';
            
            console.log('✅ Обработчик добавлен для авто ID:', carId);
        });
    }
    
    // ОЖИДАЕМ ТАБЛИЦУ
    function waitForTable() {
        var table = document.querySelector('#crm-type-item-list-1054-10parent_3_table');
        
        if (table) {
            console.log('✅ Таблица найдена');
            
            // 1. Убираем кнопки
            removeAllHistoryButtons();
            
            // 2. Перехватываем ссылки
            interceptCarLinks();
            
            // 3. Следим за изменениями
            observeTableChanges(table);
        } else {
            console.log('⏳ Таблица не найдена, ждем...');
            setTimeout(waitForTable, 1000);
        }
    }
    
    // НАБЛЮДАТЕЛЬ ЗА ИЗМЕНЕНИЯМИ
    function observeTableChanges(table) {
        if (typeof MutationObserver === 'undefined') return;
        
        var observer = new MutationObserver(function() {
            console.log('🔄 Обнаружены изменения таблицы');
            removeAllHistoryButtons();
            setTimeout(interceptCarLinks, 100);
        });
        
        observer.observe(table, {
            childList: true,
            subtree: true
        });
    }
    
    // ЗАПУСК
    if (typeof BX !== 'undefined') {
        BX.ready(function() {
            console.log('🚀 BX готов, запускаем модуль...');
            
            // Сразу убираем кнопки если есть
            removeAllHistoryButtons();
            
            // Ждем таблицу
            setTimeout(waitForTable, 1500);
        });
    } else {
        window.addEventListener('load', function() {
            setTimeout(waitForTable, 2000);
        });
    }
    
    // Экспорт для отладки
    window.debugCarHistory = {
        show: showCarHistory,
        intercept: interceptCarLinks,
        removeButtons: removeAllHistoryButtons
    };
    
    console.log('📦 Модуль истории авто инициализирован');
})();