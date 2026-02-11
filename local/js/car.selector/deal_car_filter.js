(function () {
  'use strict';

  // Настройки
  const CONFIG = {
    SMART_PROCESS_TYPE_ID: 1054,
    ENTITY_CODE: 'DYNAMICS_1054',
    CAR_FIELD_ID: 'UF_CRM_1770588718', // ID поля "Автомобиль" в сделке
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
  async function loadCarsByContact(contactId) {
    if (!CONFIG.CSRF_TOKEN) {
      console.error('CarFilter: Нет CSRF токена');
      throw new Error('Ошибка безопасности');
    }

    const formData = new FormData();
    formData.append('sessid', CONFIG.CSRF_TOKEN);
    formData.append('contactId', contactId);

    try {
      console.log('CarFilter: Запрос автомобилей для контакта', contactId);
      
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
   * Показывает попап с выбором автомобилей в стиле Битрикс24
   */
  function showCarSelection(responseData) {
    const oldPopup = document.getElementById('custom-car-popup');
    const oldOverlay = document.getElementById('car-selector-overlay');
    if (oldPopup) oldPopup.remove();
    if (oldOverlay) oldOverlay.remove();

    const carData = responseData?.ENTITIES?.[CONFIG.ENTITY_CODE];
    const cars = carData?.ITEMS || {};
    
    const overlay = document.createElement('div');
    overlay.id = 'car-selector-overlay';
    
    const popup = document.createElement('div');
    popup.id = 'custom-car-popup';
    popup.className = 'custom-car-popup';
    
    const title = document.createElement('h3');
    title.textContent = 'Выберите автомобиль';
    
    const subtitle = document.createElement('div');
    subtitle.className = 'contact-subtitle';
    subtitle.textContent = `Для контакта ID: ${state.currentContactId}`;

    const carList = document.createElement('div');
    carList.id = 'custom-car-list';

    if (Object.keys(cars).length === 0) {
      const noCarsMsg = document.createElement('div');
      noCarsMsg.className = 'no-cars-message';
      noCarsMsg.textContent = 'Для выбранного контакта нет автомобилей';
      carList.appendChild(noCarsMsg);
    } else {
      Object.values(cars).forEach(car => {
        const carItem = document.createElement('div');
        carItem.className = 'car-item';
        
        const nameSpan = document.createElement('strong');
        nameSpan.textContent = car.name;
        
        if (car.desc) {
          const descSpan = document.createElement('small');
          descSpan.textContent = car.desc;
          carItem.appendChild(nameSpan);
          carItem.appendChild(descSpan);
        } else {
          carItem.appendChild(nameSpan);
        }
        
        carItem.addEventListener('click', () => {
          carItem.style.backgroundColor = '#e5f0ff';
          setTimeout(() => {
            selectCar(car.entityId, car.name);
          }, 150);
        });
        
        carList.appendChild(carItem);
      });
    }

    const closeButton = document.createElement('button');
    closeButton.textContent = 'Закрыть';
    closeButton.type = 'button';
    
    closeButton.addEventListener('click', () => {
      popup.remove();
      overlay.remove();
    });

    popup.appendChild(title);
    popup.appendChild(subtitle);
    popup.appendChild(carList);
    popup.appendChild(closeButton);
    
    document.body.appendChild(overlay);
    document.body.appendChild(popup);
    
    popup.setAttribute('tabindex', '-1');
    popup.focus();
    
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
    
    popup.addEventListener('click', (e) => {
      e.stopPropagation();
    });
  }

  /**
   * Выбирает автомобиль и заполняет поле
   */
  function selectCar(carId, carName) {
    console.log('CarFilter: Выбор автомобиля', carId, carName);

    // 1. Удаляем overlay (затемнение фона)
    const overlay = document.getElementById('car-selector-overlay');
    if (overlay) overlay.remove();

    // 2. Удаляем попап
    const popup = document.getElementById('custom-car-popup');
    if (popup) popup.remove();

    // 3. Заполняем скрытое поле
    const field = document.getElementById(CONFIG.CAR_FIELD_ID);
    if (field) {
      field.value = carId;
      field.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // 4. Обновляем UI плитки
    const tileContainer = document.querySelector('[data-role="tile-container"]');
    if (tileContainer) {
      const oldTiles = tileContainer.querySelectorAll('[data-role="tile-item"]');
      oldTiles.forEach(tile => tile.remove());

      const tile = document.createElement('span');
      tile.setAttribute('data-role', 'tile-item');
      tile.setAttribute('data-bx-id', 'D' + carId);
      tile.className = 'ui-tile-selector-item ui-tile-selector-item-dynamic_1054';
      tile.innerHTML = `
        <span data-role="tile-item-name">${carName}</span>
        <span data-role="remove" class="ui-tile-selector-item-remove"></span>
      `;
      
      tile.querySelector('[data-role="remove"]').addEventListener('click', (e) => {
        e.preventDefault();
        tile.remove();
        if (field) field.value = '';
      });
      
      tileContainer.insertBefore(tile, tileContainer.firstChild);
    }
  }

  // ==================== ОБРАБОТЧИКИ СОБЫТИЙ ====================

  /**
   * Обработчик клика на кнопку "выбрать"
   */
  async function handleCarSelectClick(event) {
    if (state.isProcessing) return;
    state.isProcessing = true;

    try {
      const contactId = findContactId();
      
      if (!contactId) {
        console.log('CarFilter: Контакт не выбран, стандартный селектор');
        state.isProcessing = false;
        return;
      }

      event.preventDefault();
      event.stopPropagation();
      event.stopImmediatePropagation();

      state.currentContactId = contactId;
      
      const data = await loadCarsByContact(contactId);
      showCarSelection(data);
      
    } catch (error) {
      console.error('CarFilter: Ошибка при выборе автомобиля:', error);
      alert(`Ошибка: ${error.message}\nПопробуйте выбрать автомобиль без фильтра.`);
      fallbackToStandardSelector();
    } finally {
      state.isProcessing = false;
    }
  }

  /**
   * Возврат к стандартному селектору
   */
  function fallbackToStandardSelector() {
    const overlay = document.getElementById('car-selector-overlay');
    if (overlay) overlay.remove();
    
    const popup = document.getElementById('custom-car-popup');
    if (popup) popup.remove();
    
    const button = document.querySelector('[data-role="tile-select"]');
    if (button) {
      button.removeEventListener('click', handleCarSelectClick, true);
      setTimeout(() => button.click(), 50);
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
    const button = document.querySelector('[data-role="tile-select"]');
    
    if (!button) {
      console.log('CarFilter: Кнопка не найдена, повтор через 500мс');
      setTimeout(setupButtonHandler, 500);
      return;
    }

    const newButton = button.cloneNode(true);
    button.parentNode.replaceChild(newButton, button);
    newButton.addEventListener('click', handleCarSelectClick, true);
    console.log('CarFilter: Обработчик установлен');
  }

  /**
   * Мониторинг изменения контакта
   */
  function startContactMonitor() {
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
    
    setTimeout(setupButtonHandler, 1000);
    startContactMonitor();
    
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

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    setTimeout(init, 500);
  }

})();