(function() {
    class WorkdayConfirm {
        constructor() {
            this.originalButtons = new Map();
            this.init();
        }

        init() {
            this.subscribeToTimeManEvents();
        }

        subscribeToTimeManEvents() {
            BX.addCustomEvent('onTimeManDataRecieved', (data) => {
                BX.defer(() => {
                    this.replaceButtonsWithCustom(data);
                })();
            });
        }

        replaceButtonsWithCustom(dayData) {
            // Ищем все кнопки управления рабочим днём
            const buttons = document.querySelectorAll('.tm-control-panel__action');
            
            buttons.forEach(originalButton => {
                if (this.originalButtons.has(originalButton)) return;
                
                const buttonText = originalButton.querySelector('.ui-btn-text-inner')?.textContent;
                if (buttonText === 'Начать рабочий день' || buttonText === 'Возобновить') {
                    const clonedButton = originalButton.cloneNode(true);
                    this.setupCustomButton(clonedButton, originalButton, dayData, buttonText);
                    
                    originalButton.parentNode.replaceChild(clonedButton, originalButton);
                    this.originalButtons.set(clonedButton, originalButton);
                }
            });
        }

        setupCustomButton(customButton, originalButton, dayData, buttonText) {
            // Сохраняем оригинальные стили
            const originalHTML = originalButton.innerHTML;

            customButton.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                this.showBitrixPopup(originalButton, dayData, buttonText);
            };

            // Восстанавливаем внешний вид
            customButton.innerHTML = originalHTML;
            customButton.className = originalButton.className;
        }

        showBitrixPopup(originalButton, dayData, actionType) {
            const popup = new BX.PopupWindow('workday-confirm', null, {
                content: `<div style="padding: 20px; text-align: center;">
                    <div style="margin-bottom: 15px; font-size: 16px;">Вы действительно хотите ${actionType.toLowerCase()}?</div>
                </div>`,
                closeIcon: true,
                titleBar: actionType,
                overlay: true,
                autoHide: true,
                draggable: true,
                closeByEsc: true,
                buttons: [
                    new BX.PopupWindowButton({
                        text: 'Подтвердить',
                        className: 'ui-btn ui-btn-success',
                        events: {
                            click: () => {
                                this.executeOriginalAction(originalButton);
                                popup.close();
                            }
                        }
                    }),
                    new BX.PopupWindowButton({
                        text: 'Отмена',
                        className: 'ui-btn ui-btn-link',
                        events: {
                            click: () => popup.close()
                        }
                    })
                ]
            });

            popup.show();
        }

        executeOriginalAction(originalButton) {
            // Эмулируем клик по оригинальной кнопке
            const event = new MouseEvent('click', {
                view: window,
                bubbles: true,
                cancelable: true
            });
            originalButton.dispatchEvent(event);
        }
    }

    BX.ready(() => {
        new WorkdayConfirm();
    });
})();