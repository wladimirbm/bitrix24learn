(function() {
    // Локализация
    var CarMessages = {
        'CAR_LOADING': 'Загрузка истории автомобиля...',
        'CAR_ERROR_TITLE': 'Ошибка',
        'CAR_ERROR_MESSAGE': 'Не удалось загрузить информацию',
        'CAR_POPUP_TITLE': 'История автомобиля',
        'BTN_CLOSE': 'Закрыть',
        'BTN_OPEN_CARD': 'Карточка авто'
    };

    // Основная функция для показа попапа
    window.showCarDetail = function(carId, event) {
        console.log('showCarDetail вызван для авто ID:', carId);
        
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        // Проверяем, загружен ли BX
        if (typeof BX === 'undefined') {
            console.error('Библиотека Битрикс не загружена');
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
                console.log('AJAX успешно, получено символов:', html.length);
                BX.closeWait();
                
                // Создаем попап
                var popupId = 'car-detail-popup-' + carId;
                var existingPopup = BX.PopupWindowManager.getPopupById(popupId);
                
                if (existingPopup) {
                    existingPopup.destroy();
                }
                
                var popup = new BX.PopupWindow(popupId, null, {
                    content: html,
                    width: 900,
                    height: 650,
                    minHeight: 400,
                    minWidth: 600,
                    closeIcon: true,
                    title: CarMessages['CAR_POPUP_TITLE'],
                    overlay: { backgroundColor: 'rgba(0,0,0,0.5)', opacity: 80 },
                    autoHide: false,
                    draggable: { restrict: true },
                    resizable: true,
                    buttons: [
                        new BX.PopupWindowButton({
                            text: CarMessages['BTN_CLOSE'],
                            className: 'popup-window-button-close',
                            events: {
                                click: function() {
                                    popup.close();
                                }
                            }
                        })
                    ],
                    events: {
                        onPopupClose: function() {
                            this.destroy();
                        },
                        onAfterPopupShow: function() {
                            console.log('Попап успешно показан');
                        }
                    }
                });
                
                // Показываем попап
                popup.show();
                
                // Добавляем стили для попапа
                setTimeout(function() {
                    var contentDiv = popup.getContentContainer();
                    if (contentDiv) {
                        contentDiv.style.padding = '0';
                        contentDiv.style.overflow = 'hidden';
                    }
                }, 50);
            },
            onfailure: function(data, status) {
                console.error('AJAX ошибка:', status, data);
                BX.closeWait();
                
                // Если ошибка, открываем карточку авто
                if (BX.SidePanel && BX.SidePanel.Instance) {
                    BX.SidePanel.Instance.open('/crm/type/1054/details/' + carId + '/');
                } else {
                    window.open('/crm/type/1054/details/' + carId + '/', '_blank');
                }
            }
        });
    };

    // Вешаем обработчики на ссылки с авто
    function attachHandlersToCarLinks() {
        console.log('Поиск ссылок на авто...');
        
        // Ищем все ссылки на автомобили в таблице
        var links = document.querySelectorAll('table a[href*="/crm/type/1054/details/"]');
        console.log('Найдено ссылок:', links.length);
        
        links.forEach(function(link) {
            // Проверяем, не добавили ли уже обработчик
            if (link.dataset.historyHandler === 'attached') {
                return;
            }
            
            // Извлекаем ID авто из ссылки
            var href = link.getAttribute('href');
            var match = href.match(/\/details\/(\d+)/);
            if (!match) return;
            
            var carId = match[1];
            
            // Сохраняем оригинальный href на случай fallback
            link.dataset.originalHref = href;
            link.dataset.carId = carId;
            link.dataset.historyHandler = 'attached';
            
            // Меняем обработчик
            link.addEventListener('click', function(e) {
                // Проверяем, не Shift/Ctrl клик
                if (e.shiftKey || e.ctrlKey || e.metaKey) {
                    return; // Позволяем стандартное поведение для открытия в новой вкладке
                }
                
                console.log('Клик по ссылке авто ID:', carId);
                showCarDetail(carId, e);
            });
            
            // Добавляем title для подсказки
            link.title = 'Кликните для просмотра истории обслуживания. Shift+клик для открытия карточки.';
            
            console.log('Обработчик добавлен для авто ID:', carId);
        });
        
        // Также обрабатываем динамически добавляемые ссылки
        setupMutationObserver();
    }

    // Наблюдатель за изменениями DOM
    function setupMutationObserver() {
        if (typeof MutationObserver === 'undefined') return;
        
        var observer = new MutationObserver(function(mutations) {
            var shouldCheck = false;
            
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length > 0) {
                    shouldCheck = true;
                }
            });
            
            if (shouldCheck) {
                setTimeout(attachHandlersToCarLinks, 100);
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        console.log('MutationObserver запущен');
    }

    // Инициализация
    function init() {
        console.log('Инициализация модуля истории авто...');
        
        // Ждем загрузки таблицы
        function waitForTable() {
            var table = document.querySelector('table[data-table-id*="1054"], #crm-type-item-list-1054-');
            
            if (table) {
                console.log('Таблица найдена, добавляем обработчики...');
                attachHandlersToCarLinks();
                
                // Дополнительная проверка через 3 секунды
                setTimeout(attachHandlersToCarLinks, 3000);
            } else {
                console.log('Таблица не найдена, ждем...');
                setTimeout(waitForTable, 1000);
            }
        }
        
        // Запускаем после загрузки DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(waitForTable, 1000);
            });
        } else {
            setTimeout(waitForTable, 1000);
        }
        
        // Периодическая проверка
        setInterval(attachHandlersToCarLinks, 5000);
    }

    // Запускаем инициализацию
    if (typeof BX !== 'undefined') {
        BX.ready(init);
    } else {
        window.addEventListener('load', init);
    }

    // Экспортируем для отладки
    window.CarHistory = {
        showCarDetail: showCarDetail,
        attachHandlers: attachHandlersToCarLinks
    };

    console.log('Модуль истории авто загружен');
})();