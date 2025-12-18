(function () {
  BX.Otus = BX.Otus || {};
  BX.Otus.Booking = {
    init: function (doctorId) {
      document.querySelectorAll(".booking-procedure").forEach((link) => {
        link.addEventListener("click", function (e) {
          e.preventDefault();
          BX.Otus.Booking.openPopup(
            doctorId,
            this.dataset.procedure,
            this.textContent
          );
        });
      });
    },

    openPopup: function (doctorId, procedureId, procedureName) {
      if (this.popup) {
        this.popup.close();
        this.popup = null;
      }

      var popupId =
        "booking-popup-" + doctorId + "-" + procedureId + "-" + Date.now();

      var content = `
        <div style="padding: 20px; width: 300px;">
            <h3>Бронирование: ${procedureName}</h3>
            <div style="margin-bottom: 10px;">
                <label>ФИО пациента:</label>
                <input type="text" id="${popupId}-patient-name" style="width: 100%;">
            </div>
            <div style="margin-bottom: 15px;">
                <label>Дата и время:</label>
                <input type="datetime-local" id="${popupId}-datetime" style="width: 100%;">
            </div>
            <input type="hidden" id="${popupId}-doctor-id" value="${doctorId}">
            <input type="hidden" id="${popupId}-procedure-id" value="${procedureId}">
            <button onclick="BX.Otus.Booking.saveBooking('${popupId}')">Сохранить</button>
            <button onclick="BX.Otus.Booking.closePopup()">Отмена</button>
        </div>
    `;

      this.popup = new BX.PopupWindow(popupId, null, {
        content: content,
        closeIcon: true,
        overlay: true,
        autoHide: true,
        draggable: true,
        events: {
          onPopupClose: function () {
            this.popup.destroy();
            this.popup = null;
          }.bind(this),
        },
      });

      this.popup.show();
    },

    saveBooking: function (popupId) {
      var data = {
        doctorId: document.getElementById(popupId + "-doctor-id").value,
        procedureId: document.getElementById(popupId + "-procedure-id").value,
        patientName: document.getElementById(popupId + "-patient-name").value,
        datetime: document.getElementById(popupId + "-datetime").value,
        sessid: BX.bitrix_sessid(),
      };

      BX.ajax({
        url: "/local/ajax/booking.php",
        data: data,
        method: "POST",
        onsuccess: function (response) {
          var result = JSON.parse(response);
          if (result.success) {
            BX.Otus.Booking.closePopup();
            BX.UI.Notification.Center.notify({
              content: "Бронирование создано!",
              autoHideDelay: 3000,
            });
          } else {
            BX.UI.Dialogs.MessageBox.alert(result.error, "Ошибка бронирования");
          }
        },
      });
    },

    closePopup: function () {
      if (this.popup) {
        this.popup.close();
        this.popup.destroy();
        this.popup = null;
      }
    },
  };
})();
