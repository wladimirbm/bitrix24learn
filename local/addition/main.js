let originalBxOnCustomEvent = BX.onCustomEvent;

BX.onCustomEvent = function (
  eventObject,
  eventName,
  eventParams,
  secureParams
) {
  // onMenuItemHover например выбрасывает в другом порядке
  let realEventName = BX.type.isString(eventName)
    ? eventName
    : BX.type.isString(eventObject)
    ? eventObject
    : null;
  if (realEventName) {
    console.log(
      "%c" + realEventName,
      "background: #222; color: #bada55; font-weight: bold; padding: 3px 4px;"
    );
  }
  console.dir({
    eventObject: eventObject,
    eventParams: eventParams,
    secureParams: secureParams,
  });

    //  if (eventName == "onTimeManDataRecieved" && eventParams[0]["FULL"] == true) {
  //     if (eventParams[0]["STATE"] == "CLOSED") {
  //       alert("END TIME");
  //       originalBxOnCustomEvent.apply(null, arguments);
  //     } else
    //    if (eventParams[0]["STATE"] == "OPENED") { 
        //if(confirm('Точно ждешь?')) { return false; };
        // return false;
  //       eventObject.preventDefault && eventObject.preventDefault();
  //       alert("START TIME");
  //       bitrixConfirm("Вы точно готовы?").then((result) => {
  //         if (result) {
  //            originalBxOnCustomEvent.apply(null, arguments);
  //         } else {
  //           return;
        //   }
  //       });
    //    }
    //   } else
  originalBxOnCustomEvent.apply(null, arguments);
};

// Ждем загрузки DOM
BX.ready(function() {
    // Перехватываем клик по кнопке Возобновить
    document.addEventListener('click', function(event) {
        const target = event.target;
        
        // Проверяем что это кнопка Возобновить рабочего дня
        const resumeButton = target.closest('.tm-control-panel__action, .ui-btn');
        
        if (resumeButton && (
            resumeButton.textContent.includes('Возобновить') || 
            resumeButton.querySelector('.ui-icon-set.--o-refresh')
        )) {
            console.log('Найдена кнопка Возобновить - блокируем стандартное поведение');
            
            // БЛОКИРУЕМ стандартное поведение
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
            
            // Показываем наше подтверждение
            BX.UI.Dialogs.MessageBox.confirm(
                'Возобновить рабочий день?',
                function() {
                    // При подтверждении - запускаем стандартную логику возобновления
                    executeOriginalResumeHandler();
                },
                function() {
                    // При отмене - ничего не делаем
                    console.log('Возобновление рабочего дня отменено');
                    BX.UI.Notification.Center.notify({
                        content: 'Возобновление отменено',
                        autoHideDelay: 3000
                    });
                },
                'Возобновить',  // Текст кнопки OK
                'Отмена'        // Текст кнопки Cancel
            );
            
            return false;
        }
    }, true); // Используем capture phase чтобы перехватить первым
});

// Функция для выполнения стандартной логики возобновления
function executeOriginalResumeHandler() {
    console.log('Запускаем стандартную логику возобновления рабочего дня');
    
    // Отправляем AJAX запрос как делает оригинальная кнопка
    BX.ajax({
        url: '/bitrix/tools/timeman.php?action=reopen&site_id=s1&sessid=' + BX.bitrix_sessid(),
        method: 'POST',
        data: {
            newActionName: 'reopen',
            device: 'browser'
        },
        dataType: 'json',
        onsuccess: function(result) {
            console.log('Рабочий день возобновлен', result);
            // Обновляем страницу или интерфейс
            if (result && result.FULL) {
                // Можно обновить только компонент таймменеджера
                BX.reload();
            }
        },
        onfailure: function(error) {
            console.error('Ошибка возобновления рабочего дня', error);
            BX.UI.Notification.Center.notify({
                content: 'Ошибка возобновления',
                autoHideDelay: 3000
            });
        }
    });
}

/*
BX.addCustomEvent("onTimeManDataRecieved", function ($event) {
  //console.log("onTimeManDataRecieved");
  //console.log($event);

//   if ($event.preventDefault) $event.preventDefault();
//   if ($event.stopPropagation) $event.stopPropagation();

  if ($event["STATE"] == "OPENED") {
    //alert("START TIME");
    bitrixConfirm("Вы точно готовы?").then((result) => {
      if (result) {
      } else {
        return flase;
      }
    });
  }
});
*/

/*
// Глобальный флаг для блокировки
let isConfirmInProgress = false;

BX.addCustomEvent("onAjaxSuccess", function(xhr, result) {
    // Пропускаем если уже показываем подтверждение
    if (isConfirmInProgress) return;
    
    // Проверяем что это ответ от timeman.php
    if (result && result.url && result.url.includes('/bitrix/tools/timeman.php')) {
        
        let action = getTimeManAction(result);
        
        // Если это начало/переоткрытие рабочего дня
        if (action === 'start' || action === 'reopen') {
            // Устанавливаем флаг блокировки
            isConfirmInProgress = true;
            
            // БЛОКИРУЕМ дальнейшие обработчики этого события
            // Показываем MessageBox который остановит выполнение
            showBlockingMessageBox(action).then((confirmed) => {
                if (!confirmed) {
                    // При отмене - НЕ выполняем стандартную логику
                    console.log('Действие отменено:', action);
                    BX.UI.Notification.Center.notify({
                        content: 'Действие отменено',
                        autoHideDelay: 3000
                    });
                } else {
                    // При подтверждении - ВРУЧНУЮ запускаем стандартную логику
                    console.log('Действие подтверждено:', action);
                    executeStandardTimeManLogic(result);
                }
                
                isConfirmInProgress = false;
            });
            
            // ВОЗВРАЩАЕМ FALSE чтобы заблокировать другие обработчики
            return false;
        }
    }
});

// Функция для выполнения стандартной логики при подтверждении
function executeStandardTimeManLogic(result) {
    // Здесь нужно вручную вызвать то, что должно происходить при начале дня
    // Это зависит от того, как работает твой таймменеджер
    
    // Пример: обновляем данные на странице
    if (window.BXTimeman && BXTimeman.updateData) {
        BXTimeman.updateData(result);
    }
    
    // Или вызываем кастомное событие
    BX.onCustomEvent('onWorkDayStarted', [result]);
}

// БЛОКИРУЮЩАЯ функция подтверждения через MessageBox
function showBlockingMessageBox(action) {
    return new Promise((resolve) => {
        const message = action === 'reopen' 
            ? 'Продолжить рабочий день?' 
            : 'Начать рабочий день?';
            
        const okButtonText = action === 'reopen' ? 'Продолжить' : 'Начать день';
        
        BX.UI.Dialogs.MessageBox.confirm(
            message,
            function() {
                // Колбэк при подтверждении (OK)
                resolve(true);
            },
            function() {
                // Колбэк при отмене (Cancel)
                resolve(false);
            },
            okButtonText,
            'Отмена'
        );
    });
}

function getTimeManAction(result) {
    if (result.url.includes('action=start')) return 'start';
    if (result.url.includes('action=reopen')) return 'reopen';
    if (result.data && result.data.includes('newActionName=start')) return 'start';
    if (result.data && result.data.includes('newActionName=reopen')) return 'reopen';
    return null;
}

/*
function bitrixConfirm(message) {
  return new Promise((resolve) => {
    var popup = new BX.PopupWindow("bitrix-confirm", null, {
      content: BX.create("div", {
        children: [
          BX.create("div", {
            html: message,
            style: {
              padding: "20px",
              minWidth: "400px",
              marginBottom: "20px",
              fontSize: "14px",
              color: "#535c69",
            },
          }),
          BX.create("div", {
            style: {
              display: "flex",
              justifyContent: "flex-end",
              gap: "10px",
              padding: "0 20px 20px",
            },
            children: [
              new BX.UI.Button({
                text: "Отмена",
                color: BX.UI.Button.Color.LINK,
                onclick: function () {
                  popup.close();
                  resolve(false);
                },
              }).getContainer(),
              new BX.UI.Button({
                text: "Подтвердить",
                color: BX.UI.Button.Color.SUCCESS,
                onclick: function () {
                  popup.close();
                  resolve(true);
                },
              }).getContainer(),
            ],
          }),
        ],
      }),
      titleBar: "Подтверждение действия",
      closeIcon: true,
      closeByEsc: true,
      overlay: true,
      autoHide: false,
      draggable: false,
    });

    popup.show();
  });
}
*/
// Dreamsite.all = function () {
//   BX.addCustomEvent("SidePanel.Slider:onLoad", function () {
//     // $.get("/local/tools/get_offices.php", function (data) {
//     //   $(".task-detail-comments").prepend(data);
//     // });
//     $(".task-detail-comments").prepend("<H1>HELLO!!!</H1>");
//   });
// };

/*
1 - open

{
    "ID": "1",
    "STATE": "OPENED",
    "CAN_EDIT": "Y",
    "REPORT_REQ": "A",
    "TM_FREE": false,
    "INFO": {
        "DATE_START": 1764101724,
        "DATE_FINISH": "",
        "TIME_START": "83724",
        "TIME_FINISH": null,
        "DURATION": "1211",
        "TIME_LEAKS": "11715",
        "ACTIVE": true,
        "PAUSED": false,
        "CURRENT_STATUS": "OPENED",
        "RECOMMENDED_CLOSE_TIMESTAMP": 1764134124
    },
    "LAST_PAUSE": {
        "DATE_START": 1764114605,
        "DATE_FINISH": 1764114650
    },
    "SOCSERV_ENABLED": true,
    "CHECKIN_COUNTER": {
        "CLASS": "",
        "VALUE": ""
    },
    "PLANNER": {
        "CALENDAR_ENABLED": true,
        "EVENTS": [],
        "EVENT_TIME": "12:00",
        "TASKS_ENABLED": true,
        "TASKS": [],
        "TASKS_COUNT": 0,
        "TASKS_TIMER": false,
        "TASK_ON_TIMER": false,
        "MANDATORY_UFS": "N",
        "TASK_ADD_URL": "/company/personal/user/1/tasks/task/edit/0/?ADD_TO_TIMEMAN=Y"
    },
    "FULL": false
}

2- start

{
    "ID": "1",
    "STATE": "OPENED",
    "CAN_EDIT": "Y",
    "REPORT_REQ": "A",
    "TM_FREE": false,
    "INFO": {
        "DATE_START": "1764101724",
        "DATE_FINISH": "",
        "TIME_START": "83724",
        "TIME_FINISH": "",
        "DURATION": "1370",
        "TIME_LEAKS": "11724",
        "ACTIVE": true,
        "PAUSED": false,
        "CURRENT_STATUS": "OPENED",
        "RECOMMENDED_CLOSE_TIMESTAMP": "1764134124"
    },
    "LAST_PAUSE": {
        "DATE_START": "1764114804",
        "DATE_FINISH": "1764114808"
    },
    "SOCSERV_ENABLED": true,
    "SOCSERV_ENABLED_USER": false,
    "CHECKIN_COUNTER": {
        "CLASS": "",
        "VALUE": ""
    },
    "PLANNER": {
        "CALENDAR_ENABLED": true,
        "EVENTS": [
            {
                "ID": "7",
                "CAL_TYPE": "user",
                "OWNER_ID": "1",
                "CREATED_BY": "1",
                "NAME": "Встреча с заказчиком у него в офисе",
                "DATE_FROM": "26.11.2025 12:00:00",
                "DATE_TO": "26.11.2025 14:00:00",
                "TIME_FROM": "12:00",
                "TIME_TO": "14:00",
                "IMPORTANCE": "normal",
                "ACCESSIBILITY": "busy",
                "DATE_FROM_TODAY": true,
                "DATE_TO_TODAY": true,
                "SORT": "1764147600",
                "EVENT_PATH": "http://192.168.0.237/company/personal/user/1/calendar/?EVENT_ID=7&EVENT_DATE=26.11.2025"
            }
        ],
        "EVENT_TIME": "12:00",
        "TASKS_ENABLED": true,
        "TASKS": [],
        "TASKS_COUNT": "0",
        "TASKS_TIMER": false,
        "TASK_ON_TIMER": false,
        "MANDATORY_UFS": "N",
        "TASK_ADD_URL": "/company/personal/user/1/tasks/task/edit/0/?ADD_TO_TIMEMAN=Y"
    },
    "FULL": true,
    "REPORT": "",
    "REPORT_TS": "1764101725"
}


3- end

{
    "ID": "1",
    "STATE": "CLOSED",
    "CAN_EDIT": "Y",
    "CAN_OPEN": "REOPEN",
    "REPORT_REQ": "A",
    "TM_FREE": false,
    "INFO": {
        "DATE_START": "1764101724",
        "DATE_FINISH": "1764114847",
        "TIME_START": "83724",
        "TIME_FINISH": "10447",
        "DURATION": "1399",
        "TIME_LEAKS": "11724",
        "ACTIVE": true,
        "PAUSED": false,
        "CURRENT_STATUS": "CLOSED"
    },
    "LAST_PAUSE": {
        "DATE_START": "1764114813",
        "DATE_FINISH": "1764114818"
    },
    "SOCSERV_ENABLED": true,
    "SOCSERV_ENABLED_USER": false,
    "CHECKIN_COUNTER": {
        "CLASS": "",
        "VALUE": ""
    },
    "PLANNER": {
        "CALENDAR_ENABLED": true,
        "EVENTS": [
            {
                "ID": "7",
                "CAL_TYPE": "user",
                "OWNER_ID": "1",
                "CREATED_BY": "1",
                "NAME": "Встреча с заказчиком у него в офисе",
                "DATE_FROM": "26.11.2025 12:00:00",
                "DATE_TO": "26.11.2025 14:00:00",
                "TIME_FROM": "12:00",
                "TIME_TO": "14:00",
                "IMPORTANCE": "normal",
                "ACCESSIBILITY": "busy",
                "DATE_FROM_TODAY": true,
                "DATE_TO_TODAY": true,
                "SORT": "1764147600",
                "EVENT_PATH": "http://192.168.0.237/company/personal/user/1/calendar/?EVENT_ID=7&EVENT_DATE=26.11.2025"
            }
        ],
        "EVENT_TIME": "12:00",
        "TASKS_ENABLED": true,
        "TASKS": [],
        "TASKS_COUNT": "0",
        "TASKS_TIMER": false,
        "TASK_ON_TIMER": false,
        "MANDATORY_UFS": "N",
        "TASK_ADD_URL": "/company/personal/user/1/tasks/task/edit/0/?ADD_TO_TIMEMAN=Y"
    },
    "FULL": true,
    "REPORT": "",
    "REPORT_TS": "1764101725"
}

*/
