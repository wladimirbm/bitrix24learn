// ========== НАСТРОЙКИ ==========
var CarHistoryConfig = {
    messages: {
        loading: 'Загрузка истории автомобиля...',
        errorTitle: 'Ошибка',
        errorMessage: 'Не удалось загрузить информацию',
        popupTitle: 'История автомобиля',
        btnClose: 'Закрыть'
    },
    selectors: {
        carLink: 'a[href*="/crm/type/1054/details/"]',
        carButton: '.car-history-button, .car-history-btn, button[data-car-id]',
        table: '#crm-type-item-list-1054-10parent_3_table'
    }
};

// ========== ОСНОВНАЯ ФУНКЦИЯ ПОПАПА ==========
function showCarHistory(carId, event) {
    if (event) {
        if (event.preventDefault) event.preventDefault();
        if (event.stopPropagation) event.stopPropagation();
    }
    
    console.log('🚗 Открытие истории авто ID:', carId);
    
    // Проверяем BX
    if (typeof BX === 'undefined') {
        console.error('❌ Библиотека BX не загружена');
        window.open('/crm/type/1054/details/' + carId + '/', '_blank');
        return;
    }
    
    // Показываем лоадер
    if (BX.showWait) BX.showWait();
    
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
            console.log('✅ AJAX успешен, получено HTML:', html.length, 'символов');
            if (BX.closeWait) BX.closeWait();
            
            // Создаем попап
            var popupId = 'car-history-popup-' + carId;
            var existingPopup = BX.PopupWindowManager.getPopupById(popupId);
            
            if (existingPopup) {
                existingPopup.destroy();
            }
            
            var popup = new BX.PopupWindow(popupId, null, {
                content: html,
                width: 900,
                height: 650,
                closeIcon: true,
                title: CarHistoryConfig.messages.popupTitle,
                overlay: true,
                buttons: [
                    new BX.PopupWindowButton({
                        text: CarHistoryConfig.messages.btnClose,
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
        onfailure: function(data, status) {
            console.error('❌ AJAX ошибка:', status, data);
            if (BX.closeWait) BX.closeWait();
            
            // Fallback - открываем карточку
            if (BX.SidePanel && BX.SidePanel.Instance) {
                BX.SidePanel.Instance.open('/crm/type/1054/details/' + carId + '/');
            } else {
                window.open('/crm/type/1054/details/' + carId + '/', '_blank');
            }
        }
    });
}

// ========== УДАЛЕНИЕ СТАРЫХ КНОПОК ==========
function removeOldButtons() {
    var buttons = document.querySelectorAll(CarHistoryConfig.selectors.carButton);
    console.log('🗑️ Удаление старых кнопок:', buttons.length);
    
    buttons.forEach(function(button) {
        button.style.display = 'none';
        button.remove();
    });
}

// ========== ПЕРЕХВАТ ССЫЛОК ==========
function interceptCarLinks() {
    console.log('🎯 Перехват ссылок на авто...');
    
    // 1. Удаляем старые кнопки
    removeOldButtons();
    
    // 2. Находим все ссылки
    var links = document.querySelectorAll(CarHistoryConfig.selectors.carLink);
    console.log('🔗 Найдено ссылок:', links.length);
    
    // 3. Для каждой ссылки
    links.forEach(function(link) {
        // Получаем ID авто
        var href = link.getAttribute('href');
        var match = href.match(/\/details\/(\d+)/);
        if (!match) return;
        
        var carId = match[1];
        
        // Заменяем onclick
        link.onclick = function(e) {
            // Разрешаем комбинации клавиш
            if (e.shiftKey || e.ctrlKey || e.metaKey) {
                return true;
            }
            
            // Наш обработчик
            e.preventDefault();
            e.stopImmediatePropagation();
            
            console.log('🖱️ Клик по ссылке авто ID:', carId);
            showCarHistory(carId, e);
            
            return false;
        };
        
        // Добавляем подсказку
        link.title = 'Клик - история авто\nShift+клик - карточка';
        
        console.log('✅ Обработчик добавлен для авто ID:', carId);
    });
}

// ========== ОЖИДАНИЕ ТАБЛИЦЫ ==========
function waitForTableAndIntercept() {
    console.log('⏳ Ожидание таблицы с авто...');
    
    // Ищем таблицу
    var table = document.querySelector(CarHistoryConfig.selectors.table);
    
    if (table) {
        console.log('✅ Таблица найдена');
        interceptCarLinks();
        
        // Следим за изменениями таблицы
        observeTableChanges(table);
    } else {
        console.log('⏱️ Таблица не найдена, ждем...');
        setTimeout(waitForTableAndIntercept, 1000);
    }
}

// ========== НАБЛЮДАТЕЛЬ ЗА ИЗМЕНЕНИЯМИ ==========
function observeTableChanges(table) {
    if (typeof MutationObserver === 'undefined') return;
    
    var observer = new MutationObserver(function() {
        console.log('🔄 Таблица изменена, обновляем обработчики...');
        setTimeout(interceptCarLinks, 100);
    });
    
    observer.observe(table, {
        childList: true,
        subtree: true
    });
    
    console.log('👁️ Наблюдатель за таблицей запущен');
}

// ========== ЗАГРУЗОЧНЫЙ КОД ==========
function initializeCarHistory() {
    console.log('🚀 Инициализация модуля истории авто...');
    
    // Ждем загрузки BX
    if (typeof BX === 'undefined') {
        console.log('⏳ Ожидание BX...');
        setTimeout(initializeCarHistory, 500);
        return;
    }
    
    BX.ready(function() {
        console.log('✅ BX готов');
        
        // Удаляем кнопки сразу
        removeOldButtons();
        
        // Ждем таблицу
        setTimeout(waitForTableAndIntercept, 1500);
        
        // Периодическая проверка
        setInterval(interceptCarLinks, 5000);
    });
}

// ========== ГЛОБАЛЬНЫЙ ОБРАБОТЧИК (на всякий случай) ==========
document.addEventListener('click', function(e) {
    var link = e.target.closest(CarHistoryConfig.selectors.carLink);
    if (!link) return;
    
    // Проверяем, не обработали ли мы уже эту ссылку
    if (link.dataset.historyProcessed === 'true') return;
    
    // Для неперехваченных ссылок
    if (!e.shiftKey && !e.ctrlKey && !e.metaKey) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        var href = link.getAttribute('href');
        var match = href.match(/\/details\/(\d+)/);
        if (match) {
            console.log('🔄 Глобальный перехват для авто ID:', match[1]);
            showCarHistory(match[1], e);
        }
        
        return false;
    }
}, true);

// ========== ЗАПУСК ==========
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCarHistory);
} else {
    initializeCarHistory();
}

// Экспорт для отладки
window.showCarHistory = showCarHistory;
window.interceptCarLinks = interceptCarLinks;

console.log('📦 Модуль истории авто загружен');