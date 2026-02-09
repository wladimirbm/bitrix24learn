// /local/js/car_detail.js
console.log('🚗 car_detail.js загружен');

// Хранилище открытых попапов
var carHistoryPopups = {};

// 1. ФУНКЦИЯ ОТКРЫТИЯ ПОПАПА
window.showCarHistory = function(carId, event) {
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
    
    // Загружаем данные
    loadCarData(carId);
};

// 2. ЗАГРУЗКА ДАННЫХ
function loadCarData(carId) {
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
                        delete carHistoryPopups[carId];
                        this.destroy();
                    }
                }
            });
            
            // Сохраняем ссылку
            carHistoryPopups[carId] = popup;
            
            // Показываем
            popup.show();
            console.log('✅ Попап показан');
        },
        onfailure: function() {
            BX.closeWait();
            BX.UI.Dialogs.MessageBox.alert('Ошибка', 'Не удалось загрузить информацию');
        }
    });
}

// 3. ПЕРЕХВАТ ССЫЛОК (ПРОСТОЙ ВАРИАНТ)
function interceptCarLinks() {
    var links = document.querySelectorAll('a[href*="/crm/type/1054/details/"]');
    console.log('🔗 Найдено ссылок:', links.length);
    
    links.forEach(function(link) {
        // Уже обработана?
        if (link.dataset.carHistoryDone) return;
        
        var href = link.getAttribute('href');
        var match = href.match(/\/details\/(\d+)/);
        if (!match) return;
        
        var carId = match[1];
        
        // МАРКИРУЕМ
        link.dataset.carHistoryDone = 'true';
        link.dataset.carId = carId;
        
        // УБИРАЕМ HREF полностью
        link.removeAttribute('href');
        link.href = 'javascript:void(0)';
        
        // ПРОСТОЙ ОБРАБОТЧИК
        link.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('🖱️ Клик по авто ID:', carId);
            showCarHistory(carId, e);
            
            return false;
        };
        
        // ДОПОЛНИТЕЛЬНЫЙ перехват
        link.addEventListener('click', function(e) {
            e.stopImmediatePropagation();
        }, true);
        
        link.style.cursor = 'pointer';
        link.title = 'История обслуживания автомобиля';
    });
}

// 4. ОЖИДАНИЕ ТАБЛИЦЫ
function waitForTable() {
    var table = document.querySelector('#crm-type-item-list-1054-10parent_3_table');
    
    if (table) {
        console.log('✅ Таблица найдена');
        interceptCarLinks();
        
        // Следим за изменениями
        if (typeof MutationObserver !== 'undefined') {
            var observer = new MutationObserver(function() {
                setTimeout(interceptCarLinks, 100);
            });
            observer.observe(table, { childList: true, subtree: true });
        }
    } else {
        setTimeout(waitForTable, 500);
    }
}

// 5. ЗАПУСК
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Запуск модуля истории авто');
    setTimeout(waitForTable, 1000);
});

// 6. Для тестирования
window.testCarHistory = function(carId) {
    showCarHistory(carId || 1);
};

console.log('✅ car_detail.js загружен');