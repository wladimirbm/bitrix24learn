// Локализация для JS
BX.message({
    'CAR_LOADING': 'Загрузка истории автомобиля...',
    'CAR_ERROR_TITLE': 'Ошибка загрузки',
    'CAR_ERROR_MESSAGE': 'Не удалось загрузить информацию об автомобиле',
    'CAR_POPUP_TITLE': 'История обслуживания автомобиля',
    'BTN_CLOSE': 'Закрыть',
    'BTN_OPEN_CARD': 'Открыть карточку авто',
    'BTN_HISTORY': 'История'
});

function showCarDetail(carId) {
    // Показываем уведомление о загрузке
    BX.UI.Notification.Center.notify({
        content: BX.message('CAR_LOADING'),
        autoHideDelay: 2000
    });
    
    BX.ajax({
        url: '/local/components/custom/car.detail/ajax.php',
        data: {
            car_id: carId,
            sessid: BX.bitrix_sessid()
        },
        method: 'POST',
        dataType: 'html',
        onsuccess: function(html) {
            var popup = new BX.PopupWindow('car-history-' + carId, null, {
                content: html,
                width: 900,
                height: 650,
                closeIcon: true,
                title: BX.message('CAR_POPUP_TITLE'),
                buttons: [
                    new BX.PopupWindowButton({
                        text: BX.message('BTN_CLOSE'),
                        className: 'ui-btn ui-btn-primary',
                        events: { click: function() { popup.close(); } }
                    }),
                    new BX.PopupWindowButton({
                        text: BX.message('BTN_OPEN_CARD'),
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
                    })
                ]
            });
            popup.show();
        },
        onfailure: function() {
            BX.UI.Dialogs.MessageBox.alert(
                BX.message('CAR_ERROR_TITLE'),
                BX.message('CAR_ERROR_MESSAGE')
            );
        }
    });
}

// Функция для добавления кнопок в таблицу
function addHistoryButtonsToGarage() {
    var rows = document.querySelectorAll('#tab_relation_dynamic_1054 table tbody tr');
    
    rows.forEach(function(row) {
        if (row.querySelector('.history-btn-added')) return;
        
        var lastCell = row.querySelector('td:last-child');
        if (!lastCell) return;
        
        // Извлекаем ID автомобиля из ссылки
        var link = row.querySelector('a[href*="/crm/type/1054/details/"]');
        if (!link) return;
        
        var match = link.href.match(/\/details\/(\d+)/);
        if (!match) return;
        
        var carId = match[1];
        
        // Создаем кнопку
        var button = document.createElement('button');
        button.className = 'ui-btn ui-btn-light-border history-btn-added';
        button.innerHTML = '<span class="ui-btn-text">' + BX.message('BTN_HISTORY') + '</span>';
        button.style.marginLeft = '10px';
        button.style.fontSize = '12px';
        button.style.padding = '4px 10px';
        
        button.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            showCarDetail(carId);
        };
        
        lastCell.appendChild(button);
        row.classList.add('has-history-btn');
    });
}

// Запускаем после загрузки страницы
BX.ready(function() {
    // Проверяем каждую секунду, появилась ли таблица
    var checkInterval = setInterval(function() {
        var table = document.querySelector('#tab_relation_dynamic_1054 table');
        if (table) {
            addHistoryButtonsToGarage();
            clearInterval(checkInterval);
        }
    }, 1000);
});