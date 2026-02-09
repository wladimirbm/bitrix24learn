// /local/js/car_detail.js

// ГЛОБАЛЬНЫЙ ОБРАБОТЧИК на ВСЁ тело документа
document.addEventListener('click', function(e) {
    // Ищем клик по ссылке на авто
    var link = e.target.closest('a[href*="/crm/type/1054/details/"]');
    if (!link) return;
    
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();
    
    // Получаем ID авто
    var href = link.getAttribute('href') || link.href;
    var match = href.match(/\/details\/(\d+)/);
    if (!match) return;
    
    var carId = match[1];
    console.log('🚗 Клик по авто ID:', carId);
    
    // Открываем попап
    openCarPopup(carId);
    
    return false;
}, true); // capture: true - перехватываем ДО других обработчиков

// ФУНКЦИЯ ОТКРЫТИЯ ПОПАПА
function openCarPopup(carId) {
    if (typeof BX === 'undefined') {
        alert('История автомобиля (ID: ' + carId + ')');
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
            
            // Создаем попап
            var popup = new BX.PopupWindow('car-popup-' + carId + '-' + Date.now(), null, {
                content: html,
                width: 900,
                height: 650,
                closeIcon: true,
                title: 'История автомобиля',
                overlay: true,
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
                ]
            });
            
            popup.show();
            console.log('✅ Попап открыт');
        },
        onfailure: function() {
            BX.closeWait();
            console.error('❌ Ошибка AJAX');
        }
    });
}

// УДАЛЯЕМ КНОПКИ если есть
setTimeout(function() {
    document.querySelectorAll('.car-history-button, .car-history-btn').forEach(function(btn) {
        btn.remove();
    });
}, 1000);

console.log('✅ Обработчик истории авто установлен');