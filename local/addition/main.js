let originalBxOnCustomEvent = BX.onCustomEvent;

BX.onCustomEvent = function (
  eventObject,
  eventName,
  eventParams,
  secureParams
) {
    console.log('main.onCustomEvent - ' + eventName, eventObject, eventParams, secureParams); 
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
  originalBxOnCustomEvent.apply(null, arguments);
};


BX.addCustomEvent("onTimeManDataRecieved", function () {
  console.log("onTimeManDataRecieved");
});

// Dreamsite.all = function () {
//   BX.addCustomEvent("SidePanel.Slider:onLoad", function () {
//     // $.get("/local/tools/get_offices.php", function (data) {
//     //   $(".task-detail-comments").prepend(data);
//     // });
//     $(".task-detail-comments").prepend("<H1>HELLO!!!</H1>");
//   });
// };
