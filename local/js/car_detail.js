// /local/js/car_detail.js
console.log('🚗 car_detail.js загружен');

// Хранилище открытых попапов (защита от дублей)
var carHistoryPopups = {};

// 1. УДАЛЕНИЕ КНОПОК "ИСТОРИЯ"
function removeHistoryButtons() {
    var buttons = document.querySelectorAll('.car-history-button, .car-history-btn');
    buttons.forEach(function(btn) {
        btn.style.display = 'none';
        setTimeout(function() {
            if (btn.parentNode) btn.parentNode.removeChild(btn);
        }, 100);
    });
    if (buttons.length > 0) {
        console.log('🗑️ Удалено кнопок:', buttons.length);
    }
}

// 2. ФУНКЦИЯ ОТКРЫТИЯ ПОПАПА
window.showCarHistory = function(carId, event) {
    // Останавливаем ВСЁ
    if (event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
    }
    
    console.log('📱 Открытие истории авто ID:', carId);
    
    // Закрываем предыдущий попап этого авто
    if (carHistoryPopups[carId]) {
        try {
            carHistoryPopups[carId].close();
        } catch (e) {}
        delete carHistoryPopups[carId];
    }
    
    // Проверяем BX
    if (typeof BX === 'undefined') {
        console.error('❌ BX не загружен');
        return;
    }
    
    // Проверяем popup модуль
    if (typeof BX.PopupWindow === 'undefined') {
        BX.load(['popup'], function() {
            loadCarData(carId);
        });
    } else {
        loadCarData(carId);
    }
};

// 3. ЗАГРУЗКА ДАННЫХ
function loadCarData(carId) {
    // Показываем лоадер
    BX.showWait();
    
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
            console.log('✅ Данные загружены');
            
            // Уникальный ID для попапа
            var popupId = 'car-history-' + carId + '-' + Date.now();
            
            // Создаем попап
            var popup = new BX.PopupWindow(popupId, null, {
                content: html,
                width: 900,
                height: 650,
                closeIcon: true,
                title: 'История автомобиля',
                overlay: true,
                autoHide: false,
                buttons: [
                    new BX.PopupWindowButton({
                        text: 'Закрыть',
                        className: 'ui-btn ui-btn-primary',
                        events: {
                            click: function() {
                                this.popupWindow.close();
                            }
                        }
                    })
                ],
                events: {
                    onPopupClose: function() {
                        // Удаляем из хранилища при закрытии
                        delete carHistoryPopups[carId];
                        this.destroy();
                    }
                }
            });
            
            // Сохраняем ссылку на попап
            carHistoryPopups[carId] = popup;
            
            // Показываем
            popup.show();
            console.log('✅ Попап показан');
        },
        onfailure: function() {
            BX.closeWait();
            console.error('❌ Ошибка AJAX');
            BX.UI.Dialogs.MessageBox.alert('Ошибка', 'Не удалось загрузить информацию');
        }
    });
}

// 4. ПЕРЕХВАТ ССЫЛОК (ГЛАВНОЕ!)
function interceptCarLinks() {
    var links = document.querySelectorAll('a[href*="/crm/type/1054/details/"]');
    console.log('🔗 Найдено ссылок на авто:', links.length);
    
    links.forEach(function(link) {
        // Уже обработана?
        if (link.dataset.carHistoryIntercepted === 'true') {
            return;
        }
        
        var href = link.getAttribute('href');
        var match = href.match(/\/details\/(\d+)/);
        if (!match) return;
        
        var carId = match[1];
        
        // Маркируем как обработанную
        link.dataset.carHistoryIntercepted = 'true';
        link.dataset.carId = carId;
        
        // СОХРАНЯЕМ оригинальный href (для shift+клик)
        var originalHref = href;
        
        // СИЛЬНЫЙ ПЕРЕХВАТ: заменяем onclick полностью
        link.onclick = function(e) {
            // Разрешаем только shift/ctrl+клик для открытия карточки
            if (e.shiftKey || e.ctrlKey || e.metaKey) {
                // Открываем оригинальную карточку
                if (e.shiftKey || e.ctrlKey) {
                    if (BX.SidePanel && BX.SidePanel.Instance) {
                        BX.SidePanel.Instance.open(originalHref);
                    } else {
                        window.open(originalHref, '_blank');
                    }
                }
                return true;
            }
            
            // Обычный клик - ОСТАНАВЛИВАЕМ ВСЁ
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            console.log('🖱️ Клик перехвачен для авто ID:', carId);
            showCarHistory(carId, e);
            
            return false;
        };
        
        // ДОПОЛНИТЕЛЬНЫЙ обработчик на capture phase (перехватывает раньше всех)
        link.addEventListener('click', function(e) {
            if (!e.shiftKey && !e.ctrlKey && !e.metaKey) {
                e.stopImmediatePropagation();
            }
        }, true); // capture phase!
        
        // Меняем курсор и подсказку
        link.style.cursor = 'pointer';
        link.title = 'Клик — история авто\nShift/Ctrl+клик — карточка';
        
        console.log('✅ Обработчик установлен для авто ID:', carId);
    });
}

// 5. ОЖИДАНИЕ ТАБЛИЦЫ
function waitForTableAndInit() {
    var table = document.querySelector('#crm-type-item-list-1054-10parent_3_table');
    
    if (table) {
        console.log('✅ Таблица найдена');
        
        // 1. Удаляем кнопки
        removeHistoryButtons();
        
        // 2. Перехватываем ссылки
        interceptCarLinks();
        
        // 3. Следим за изменениями таблицы
        if (typeof MutationObserver !== 'undefined') {
            var observer = new MutationObserver(function() {
                console.log('🔄 Таблица обновилась');
                setTimeout(function() {
                    removeHistoryButtons();
                    interceptCarLinks();
                }, 100);
            });
            
            observer.observe(table, {
                childList: true,
                subtree: true
            });
            
            console.log('👁️ Наблюдатель за таблицей запущен');
        }
    } else {
        console.log('⏳ Ждем таблицу...');
        setTimeout(waitForTableAndInit, 500);
    }
}

// 6. ИНИЦИАЛИЗАЦИЯ
function initCarHistory() {
    console.log('🚀 Инициализация модуля истории авто');
    
    // Сразу удаляем кнопки если есть
    removeHistoryButtons();
    
    // Ждем таблицу
    setTimeout(waitForTableAndInit, 1000);
    
    // Периодическая проверка (на случай динамической загрузки)
    setInterval(function() {
        removeHistoryButtons();
        interceptCarLinks();
    }, 3000);
}

// 7. ЗАПУСК
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCarHistory);
} else {
    initCarHistory();
}

// 8. ТЕСТОВЫЕ ФУНКЦИИ
window.debugCarHistory = {
    testPopup: function(carId) {
        showCarHistory(carId || 1);
    },
    checkLinks: function() {
        var links = document.querySelectorAll('a[href*="/crm/type/1054/details/"]');
        console.log('🔍 Проверка ссылок:', links.length);
        links.forEach(function(link, i) {
            console.log(i + 1 + '.', link.href, '- intercepted:', link.dataset.carHistoryIntercepted);
        });
    },
    removeAllButtons: removeHistoryButtons
};

console.log('✅ car_detail.js инициализирован');