(function() {
    BX.Otus = BX.Otus || {};
    BX.Otus.Booking = {
        init: function(doctorId) {
            document.querySelectorAll('.booking-procedure').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    BX.Otus.BookingPopup.openPopup(
                        doctorId,
                        this.dataset.procedure,
                        this.textContent
                    );
                });
            });
        },

        openPopup: function(doctorId, procedureId, procedureName) {
            var content = `
                <div style="padding: 20px; width: 300px;">
                    <h3>Бронирование: ${procedureName}</h3>
                    <div style="margin-bottom: 10px;">
                        <label>ФИО пациента:</label>
                        <input type="text" id="booking-patient-name" style="width: 100%;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label>Дата и время:</label>
                        <input type="datetime-local" id="booking-datetime" style="width: 100%;">
                    </div>
                    <input type="hidden" id="booking-doctor-id" value="${doctorId}">
                    <input type="hidden" id="booking-procedure-id" value="${procedureId}">
                    <button onclick="BX.Otus.BookingPopup.saveBooking()">Сохранить</button>
                    <button onclick="BX.Otus.BookingPopup.closePopup()">Отмена</button>
                </div>
            `;

            this.popup = new BX.PopupWindow('booking-popup', null, {
                content: content,
                closeIcon: true,
                overlay: true,
                autoHide: true,
                draggable: true
            });

            this.popup.show();
        },

        saveBooking: function() {
            var data = {
                doctorId: document.getElementById('booking-doctor-id').value,
                procedureId: document.getElementById('booking-procedure-id').value,
                patientName: document.getElementById('booking-patient-name').value,
                datetime: document.getElementById('booking-datetime').value,
                sessid: BX.bitrix_sessid()
            };

            BX.ajax({
                url: '/local/ajax/booking.php',
                data: data,
                method: 'POST',
                onsuccess: function(response) {
                    var result = JSON.parse(response);
                    if (result.success) {
                        BX.Otus.BookingPopup.closePopup();
                        BX.UI.Notification.Center.notify({
                            content: 'Бронирование создано!',
                            autoHideDelay: 3000
                        });
                    } else {
                        BX.UI.Dialogs.MessageBox.alert(
                            result.error,
                            'Ошибка бронирования'
                        );
                    }
                }
            });
        },

        closePopup: function() {
            if (this.popup) {
                this.popup.close();
            }
        }
    };
})();