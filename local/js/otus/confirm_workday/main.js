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
            const selectors = [
                '.tm-control-panel__action:has(.ui-btn-text-inner:contains("Начать рабочий день"))',
                '.tm-control-panel__action:has(.ui-btn-text-inner:contains("Возобновить"))'
            ];

            selectors.forEach(selector => {
                const buttons = document.querySelectorAll(selector);
                buttons.forEach(originalButton => {
                    if (this.originalButtons.has(originalButton)) return;

                    const clonedButton = originalButton.cloneNode(true);
                    this.setupCustomButton(clonedButton, originalButton, dayData);
                    
                    originalButton.parentNode.replaceChild(clonedButton, originalButton);
                    this.originalButtons.set(clonedButton, originalButton);
                });
            });
        }

        setupCustomButton(customButton, originalButton, dayData) {
            customButton.style.position = 'relative';
            
            // Сохраняем оригинальные стили и обработчики
            const originalOnClick = originalButton.onclick;
            const originalHTML = originalButton.innerHTML;

            customButton.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                this.showBitrixPopup(originalButton, dayData);
            };

            // Восстанавливаем внешний вид
            customButton.innerHTML = originalHTML;
            customButton.className = originalButton.className;
        }

        showBitrixPopup(originalButton, dayData) {
            const actionType = originalButton.querySelector('.ui-btn-text-inner').textContent;
            
            const popup = new BX.PopupWindow('workday-confirm', null, {
                content: `<div style="padding: 20px; text-align: center;">
                    <div style="margin-bottom: 15px; font-size: 16px;">Вы действительно хотите ${actionType.toLowerCase()}?</div>
                    <div style="color: #828b95; font-size: 14px;">Текущее время: ${new Date().toLocaleTimeString()}</div>
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
                                this.executeOriginalAction(originalButton, dayData);
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

        executeOriginalAction(originalButton, dayData) {
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