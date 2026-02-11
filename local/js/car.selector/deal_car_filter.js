(function () {
  'use strict';

  // Настройки
  const CONFIG = {
    SMART_PROCESS_TYPE_ID: 1054,
    ENTITY_CODE: 'DYNAMICS_1054',
    CAR_FIELD_ID: 'UF_CRM_1770588718', // ID поля "Автомобиль" в сделке
    // API_ACTION: 'local.car.selector:getCars', // Наш кастомный экшн
    CSRF_TOKEN: document.querySelector('meta[name="csrf-token"]')?.content || 
                document.querySelector('input[name="sessid"]')?.value
  };

  // Состояние приложения
  const state = {
    currentContactId: null,
    isProcessing: false,
    isInitialized: false
  };

  // ==================== УТИЛИТЫ ====================

  /**
   * Находит ID контакта в DOM
   */
  function findContactId() {
    // Ищем по data-cid (самый надежный способ)
    const elements = document.querySelectorAll('[data-cid]');
    
    for (let element of elements) {
      const dataCid = element.getAttribute('data-cid');
      if (dataCid && dataCid.startsWith('CONTACT_')) {
        const match = dataCid.match(/CONTACT_(\d+)_/);
        if (match && match[1]) {
          console.debug('CarFilter: Найден контакт ID:', match[1]);
          return parseInt(match[1], 10);
        }
      }
    }
    
    // Альтернативный поиск по имени поля
    const contactField = document.querySelector('[name*="CONTACT"], [id*="CONTACT"]');
    if (contactField && contactField.value) {
      return parseInt(contactField.value, 10);
    }
    
    return null;
  }

  // ==================== API ВЗАИМОДЕЙСТВИЕ ====================

  /**
   * Загружает автомобили для указанного контакта через наш API
   */
  /**
 * Загружает автомобили для указанного контакта через наш API
 */
async function loadCarsByContact(contactId) {
    if (!CONFIG.CSRF_TOKEN) {
        console.error('CarFilter: Нет CSRF токена');
        throw new Error('Ошибка безопасности');
    }

    // Формируем данные для запроса
    const formData = new FormData();
    formData.append('sessid', CONFIG.CSRF_TOKEN);
    formData.append('contactId', contactId);

    try {
        console.log('CarFilter: Запрос автомобилей для контакта', contactId);
        
        // ВАЖНО: Меняем URL на наш обработчик
        const response = await fetch('/local/ajax/car_selector.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const data = await response.json();
        console.log('CarFilter: Ответ API:', data);

        if (data.status === 'success') {
            return data.data;
        } else {
            const errorMsg = data.errors?.[0]?.message || 'Неизвестная ошибка сервера';
            throw new Error(errorMsg);
        }
        
    } catch (error) {
        console.error('CarFilter: Ошибка запроса:', error);
        throw error;
    }
}

  // ==================== UI КОМПОНЕНТЫ ====================

  /**
   * Показывает попап с выбором автомобилей
   */
//   function showCarSelection(responseData) {
//     // Удаляем старый попап, если есть
//     const oldPopup = document.getElementById('custom-car-popup');
//     if (oldPopup) oldPopup.remove();

//     // Проверяем данные
//     const carData = responseData?.ENTITIES?.[CONFIG.ENTITY_CODE];
//     const cars = carData?.ITEMS || {};
    
//     if (Object.keys(cars).length === 0) {
//       alert('Для выбранного контакта нет автомобилей');
//       return;
//     }

//     // Создаем попап
//     const popup = document.createElement('div');
//     popup.id = 'custom-car-popup';
//     popup.className = 'custom-car-popup';
    
//     // Стили через CSS-класс (добавь в свой CSS файл)
//     Object.assign(popup.style, {
//       position: 'fixed',
//       top: '50%',
//       left: '50%',
//       transform: 'translate(-50%, -50%)',
//       background: 'white',
//       border: '2px solid #2a72cc',
//       borderRadius: '5px',
//       padding: '20px',
//       zIndex: '10000',
//       boxShadow: '0 0 20px rgba(0,0,0,0.3)',
//       maxWidth: '500px',
//       maxHeight: '400px',
//       overflowY: 'auto',
//       fontFamily: 'Arial, sans-serif'
//     });

//     // Заголовок
//     const title = document.createElement('h3');
//     title.textContent = 'Выберите автомобиль';
//     title.style.marginTop = '0';
//     title.style.color = '#2a72cc';
    
//     const subtitle = document.createElement('div');
//     subtitle.textContent = `Для контакта ID: ${state.currentContactId}`;
//     subtitle.style.marginBottom = '15px';
//     subtitle.style.color = '#666';

//     // Список автомобилей
//     const carList = document.createElement('div');
//     carList.id = 'custom-car-list';
//     carList.style.marginBottom = '15px';

//     Object.values(cars).forEach(car => {
//       const carItem = document.createElement('div');
//       carItem.className = 'car-item';
//       Object.assign(carItem.style, {
//         padding: '8px',
//         borderBottom: '1px solid #eee',
//         cursor: 'pointer',
//         transition: 'background-color 0.2s'
//       });
      
//       carItem.innerHTML = `<strong>${car.name}</strong>${car.desc ? `<br><small>${car.desc}</small>` : ''}`;
      
//       carItem.addEventListener('mouseenter', () => {
//         carItem.style.backgroundColor = '#f5f5f5';
//       });
      
//       carItem.addEventListener('mouseleave', () => {
//         carItem.style.backgroundColor = 'transparent';
//       });
      
//       carItem.addEventListener('click', () => {
//         selectCar(car.entityId, car.name);
//       });
      
//       carList.appendChild(carItem);
//     });

//     // Кнопка закрытия
//     const closeButton = document.createElement('button');
//     closeButton.textContent = 'Закрыть';
//     Object.assign(closeButton.style, {
//       padding: '8px 15px',
//       background: '#2a72cc',
//       color: 'white',
//       border: 'none',
//       borderRadius: '3px',
//       cursor: 'pointer'
//     });
    
//     closeButton.addEventListener('click', () => {
//       popup.remove();
//     });

//     // Собираем попап
//     popup.appendChild(title);
//     popup.appendChild(subtitle);
//     popup.appendChild(carList);
//     popup.appendChild(closeButton);
    
//     // Закрытие по клику вне попапа
//     popup.addEventListener('click', (e) => {
//       if (e.target === popup) {
//         popup.remove();
//       }
//     });

//     document.body.appendChild(popup);
//   }


/**
 * Показывает попап с выбором автомобилей в стиле Битрикс24
 */
function showCarSelection(responseData) {
    // Удаляем старый попап
    const oldPopup = document.getElementById('custom-car-popup');
    if (oldPopup) oldPopup.remove();

    // Проверяем данные
    const carData = responseData?.ENTITIES?.[CONFIG.ENTITY_CODE];
    const cars = carData?.ITEMS || {};
    
    // Создаем попап
    const popup = document.createElement('div');
    popup.id = 'custom-car-popup';
    popup.className = 'custom-car-popup';
    
    // Заголовок
    const title = document.createElement('h3');
    title.textContent = 'Выберите автомобиль';
    
    // Подзаголовок с ID контакта
    const subtitle = document.createElement('div');
    subtitle.className = 'contact-subtitle';
    subtitle.textContent = `Для контакта ID: ${state.currentContactId}`;

    // Список автомобилей
    const carList = document.createElement('div');
    carList.id = 'custom-car-list';

    if (Object.keys(cars).length === 0) {
        // Сообщение "нет автомобилей"
        const noCarsMsg = document.createElement('div');
        noCarsMsg.className = 'no-cars-message';
        noCarsMsg.textContent = 'Для выбранного контакта нет автомобилей';
        carList.appendChild(noCarsMsg);
    } else {
        // Добавляем автомобили
        Object.values(cars).forEach(car => {
            const carItem = document.createElement('div');
            carItem.className = 'car-item';
            
            // Основное название
            const nameSpan = document.createElement('strong');
            nameSpan.textContent = car.name;
            
            // Дополнительное описание (если есть)
            if (car.desc) {
                const descSpan = document.createElement('small');
                descSpan.textContent = car.desc;
                carItem.appendChild(nameSpan);
                carItem.appendChild(descSpan);
            } else {
                carItem.appendChild(nameSpan);
            }
            
            // Обработчик клика
            carItem.addEventListener('click', () => {
                // Добавляем визуальную обратную связь
                carItem.style.backgroundColor = '#e5f0ff';
                setTimeout(() => {
                    selectCar(car.entityId, car.name);
                }, 150);
            });
            
            carList.appendChild(carItem);
        });
    }

    // Кнопка закрытия
    const closeButton = document.createElement('button');
    closeButton.textContent = 'Закрыть';
    closeButton.type = 'button';
    
    // Обработчики для кнопки
    closeButton.addEventListener('click', () => popup.remove());
    closeButton.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            popup.remove();
        }
    });

    // Затемнение фона
    const overlay = document.createElement('div');
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.4);
        z-index: 9999;
    `;
    overlay.id = 'car-selector-overlay';
    
    // Собираем попап
    popup.appendChild(title);
    popup.appendChild(subtitle);
    popup.appendChild(carList);
    popup.appendChild(closeButton);
    
    // Добавляем overlay и попап
    document.body.appendChild(overlay);
    document.body.appendChild(popup);
    
    // Фокус на попапе для доступности
    popup.setAttribute('tabindex', '-1');
    popup.focus();
    
    // Закрытие по клику на overlay или клавише ESC
    overlay.addEventListener('click', () => {
        popup.remove();
        overlay.remove();
    });
    
    popup.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            popup.remove();
            overlay.remove();
        }
    });
    
    // Предотвращаем закрытие при клике внутри попапа
    popup.addEventListener('click', (e) => {
        e.stopPropagation();
    });
}


  /**
   * Выбирает автомобиль и заполняет поле
   */
  function selectCar(carId, carName) {
    console.log('CarFilter: Выбор автомобиля', carId, carName);

    // 1. Заполняем скрытое поле
    const field = document.getElementById(CONFIG.CAR_FIELD_ID);
    if (field) {
      field.value = carId;
      field.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // 2. Обновляем UI плитки
    const tileContainer = document.querySelector('[data-role="tile-container"]');
    if (tileContainer) {
      // Очищаем старые плитки
      const oldTiles = tileContainer.querySelectorAll('[data-role="tile-item"]');
      oldTiles.forEach(tile => tile.remove());

      // Добавляем новую плитку
      const tile = document.createElement('span');
      tile.setAttribute('data-role', 'tile-item');
      tile.setAttribute('data-bx-id', 'D' + carId);
      tile.className = 'ui-tile-selector-item ui-tile-selector-item-dynamic_1054';
      tile.innerHTML = `
        <span data-role="tile-item-name">${carName}</span>
        <span data-role="remove" class="ui-tile-selector-item-remove"></span>
      `;
      
      // Обработчик удаления
      tile.querySelector('[data-role="remove"]').addEventListener('click', (e) => {
        e.preventDefault();
        tile.remove();
        if (field) field.value = '';
      });
      
      tileContainer.insertBefore(tile, tileContainer.firstChild);
    }

    // 3. Закрываем попап
    const popup = document.getElementById('custom-car-popup');
    if (popup) popup.remove();
  }

  // ==================== ОБРАБОТЧИКИ СОБЫТИЙ ====================

  /**
   * Обработчик клика на кнопку "выбрать"
   */
  async function handleCarSelectClick(event) {
    // Защита от двойного клика
    if (state.isProcessing) return;
    state.isProcessing = true;

    try {
      // Ищем контакт
      const contactId = findContactId();
      
      if (!contactId) {
        // Если контакт не выбран - стандартное поведение
        console.log('CarFilter: Контакт не выбран, стандартный селектор');
        state.isProcessing = false;
        return;
      }

      // Блокируем стандартное поведение
      event.preventDefault();
      event.stopPropagation();
      event.stopImmediatePropagation();

      // Обновляем состояние
      state.currentContactId = contactId;
      
      // Загружаем автомобили через наш API
      const data = await loadCarsByContact(contactId);
      
      // Показываем результаты
      showCarSelection(data);
      
    } catch (error) {
      console.error('CarFilter: Ошибка при выборе автомобиля:', error);
      alert(`Ошибка: ${error.message}\nПопробуйте выбрать автомобиль без фильтра.`);
      
      // Fallback: стандартный селектор
      fallbackToStandardSelector();
    } finally {
      state.isProcessing = false;
    }
  }

  /**
   * Возврат к стандартному селектору
   */
  function fallbackToStandardSelector() {
    const button = document.querySelector('[data-role="tile-select"]');
    if (button) {
      // Временно отключаем наш обработчик
      button.removeEventListener('click', handleCarSelectClick, true);
      // Имитируем клик
      setTimeout(() => button.click(), 50);
      // Возвращаем обработчик
      setTimeout(() => {
        button.addEventListener('click', handleCarSelectClick, true);
      }, 500);
    }
  }

  // ==================== ИНИЦИАЛИЗАЦИЯ ====================

  /**
   * Настройка обработчика кнопки
   */
  function setupButtonHandler() {
    // Ищем кнопку "выбрать"
    const button = document.querySelector('[data-role="tile-select"]');
    
    if (!button) {
      // Если кнопка еще не загружена, ждем
      console.log('CarFilter: Кнопка не найдена, повтор через 500мс');
      setTimeout(setupButtonHandler, 500);
      return;
    }

    // Удаляем старые обработчики (если были)
    const newButton = button.cloneNode(true);
    button.parentNode.replaceChild(newButton, button);

    // Добавляем наш обработчик
    newButton.addEventListener('click', handleCarSelectClick, true);
    console.log('CarFilter: Обработчик установлен');
  }

  /**
   * Мониторинг изменения контакта
   */
  function startContactMonitor() {
    // Проверяем каждую секунду, не изменился ли контакт
    setInterval(() => {
      const contactId = findContactId();
      if (contactId && contactId !== state.currentContactId) {
        console.log('CarFilter: Контакт изменен на ID:', contactId);
        state.currentContactId = contactId;
      }
    }, 1000);
  }

  /**
   * Инициализация модуля
   */
  function init() {
    if (state.isInitialized) return;
    
    console.log('CarFilter: Инициализация...');
    
    // 1. Настраиваем кнопку
    setTimeout(setupButtonHandler, 1000);
    
    // 2. Запускаем монитор контакта
    startContactMonitor();
    
    // 3. Делаем публичные методы для отладки
    window.CarFilter = {
      getCurrentContact: () => state.currentContactId,
      debug: () => {
        console.log('=== CarFilter Debug ===');
        console.log('Контакт:', state.currentContactId);
        console.log('Поле авто:', document.getElementById(CONFIG.CAR_FIELD_ID));
        console.log('Кнопка:', document.querySelector('[data-role="tile-select"]'));
      },
      reload: () => {
        state.isInitialized = false;
        init();
      }
    };
    
    state.isInitialized = true;
    console.log('CarFilter: Инициализация завершена');
  }

  // ==================== ЗАПУСК ====================

  // Ждем полной загрузки DOM
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    setTimeout(init, 500);
  }

})();