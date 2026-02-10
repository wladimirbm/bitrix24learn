/**
 * Фильтрация автомобилей по выбранному контакту в сделке
 * УПРОЩЕННЫЙ РАБОЧИЙ ВАРИАНТ
 */

(function () {
  "use strict";

  const SMART_PROCESS_TYPE_ID = 1054;
  const ENTITY_CODE = "DYNAMICS_" + SMART_PROCESS_TYPE_ID;
  const CAR_FIELD_ID = "UF_CRM_1770588718";

  let currentContactId = null;
  let isInitialized = false;

  console.log("DealCarFilter: Упрощенная версия загружена");

  // ==================== ОСНОВНЫЕ ФУНКЦИИ ====================

  /**
   * Найти ID контакта в DOM - ПРОСТАЯ ВЕРСИЯ
   */
  function findContactId() {
    // Просто ищем элемент с data-cid, начинающимся на CONTACT_
    const elements = document.querySelectorAll("[data-cid]");

    for (let element of elements) {
      const dataCid = element.getAttribute("data-cid");
      if (dataCid && dataCid.startsWith("CONTACT_")) {
        const match = dataCid.match(/CONTACT_(\d+)_/);
        if (match && match[1]) {
          console.log("DealCarFilter: Найден контакт ID:", match[1]);
          return match[1];
        }
      }
    }

    return null;
  }

  /**
   * Обработчик клика на кнопку "выбрать"
   */
  function handleCarSelectClick(event) {
    console.log('DealCarFilter: Клик на кнопку "выбрать"');

    // ПРОВЕРКА КОНТАКТА ПЕРЕД КАЖДЫМ КЛИКОМ
    const contactId = findContactId();
    console.log("DealCarFilter: Контакт при клике:", contactId);

    // Если контакт НЕ выбран - стандартное поведение
    if (!contactId) {
      console.log("DealCarFilter: Контакт не выбран, стандартный селектор");
      return; // Позволяем стандартному обработчику работать
    }

    // Если контакт ВЫБРАН - наша логика
    console.log("DealCarFilter: Контакт выбран, фильтруем автомобили");
    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();

    // Загружаем данные с фильтром
    loadFilteredCars(contactId);

    return false;
  }

  function loadFilteredCars(contactId) {
    console.log("DealCarFilter: Загрузка автомобилей для контакта", contactId);

    // Получаем CSRF токен
    const csrfToken =
        document.querySelector('meta[name="csrf-token"]')?.content ||
        document.querySelector('input[name="sessid"]')?.value;

    if (!csrfToken) {
        console.error("DealCarFilter: Нет CSRF токена");
        return;
    }

    const formData = new FormData();
    formData.append("mode", "ajax");
    formData.append("c", "bitrix:main.ui.selector");
    formData.append("action", "getData");
    formData.append("sessid", csrfToken);

    // Все оригинальные параметры (как в твоем примере)
    formData.append("data[options][useNewCallback]", "Y");
    formData.append("data[options][eventInit]", "BX.Main.User.SelectorController::init");
    formData.append("data[options][eventOpen]", "BX.Main.User.SelectorController::open");
    formData.append("data[options][lazyLoad]", "Y");
    formData.append("data[options][context]", "crmEntityCreate");
    formData.append("data[options][contextCode]", "");
    formData.append("data[options][enableSonetgroups]", "N");
    formData.append("data[options][enableUsers]", "N");
    formData.append("data[options][useClientDatabase]", "N");
    formData.append("data[options][enableAll]", "N");
    formData.append("data[options][enableDepartments]", "N");
    formData.append("data[options][enableCrm]", "Y");
    formData.append("data[options][crmPrefixType]", "SHORT");
    formData.append("data[options][enableCrmDynamics][1054]", "Y");
    formData.append("data[options][addTabCrmDynamics][1054]", "N");
    formData.append("data[options][addTabCrmContacts]", "N");
    formData.append("data[options][addTabCrmCompanies]", "N");
    formData.append("data[options][addTabCrmLeads]", "N");
    formData.append("data[options][addTabCrmDeals]", "N");
    formData.append("data[options][addTabCrmOrders]", "N");
    formData.append("data[options][addTabCrmQuotes]", "N");
    formData.append("data[options][addTabCrmSmartInvoices]", "N");
    formData.append("data[options][crmDynamicTitles][DYNAMICS_1040]", "Марка автомобиля");
    formData.append("data[options][crmDynamicTitles][DYNAMICS_1046]", "Модель автомобиля");
    formData.append("data[options][crmDynamicTitles][DYNAMICS_1054]", "Гараж");
    formData.append("data[options][crmDynamicTitles][DYNAMICS_1058]", "Заявка на закупку");
    formData.append("data[options][multiple]", "N");
    formData.append("data[options][extranetContext]", "false");
    formData.append("data[options][useSearch]", "N");
    formData.append("data[options][userNameTemplate]", "#NAME# #LAST_NAME#");
    formData.append("data[options][allowEmailInvitation]", "N");
    formData.append("data[options][departmentSelectDisable]", "Y");
    formData.append("data[options][allowAddUser]", "N");
    formData.append("data[options][allowAddCrmContact]", "N");
    formData.append("data[options][allowAddSocNetGroup]", "N");
    formData.append("data[options][allowSearchEmailUsers]", "N");
    formData.append("data[options][allowSearchCrmEmailUsers]", "N");
    formData.append("data[options][allowSearchNetworkUsers]", "N");
    
    // Entity Types
    formData.append("data[entityTypes][GROUPS][options][context]", "crmEntityCreate");
    formData.append("data[entityTypes][GROUPS][options][enableAll]", "N");
    formData.append("data[entityTypes][GROUPS][options][enableEmpty]", "N");
    formData.append("data[entityTypes][GROUPS][options][enableUserManager]", "N");
    formData.append("data[entityTypes][EMAILUSERS][options][allowAdd]", "N");
    formData.append("data[entityTypes][EMAILUSERS][options][allowAddCrmContact]", "N");
    formData.append("data[entityTypes][EMAILUSERS][options][allowSearchCrmEmailUsers]", "N");
    formData.append("data[entityTypes][EMAILUSERS][options][addTab]", "N");
    formData.append("data[entityTypes][DYNAMICS_1054][options][enableSearch]", "Y");
    formData.append("data[entityTypes][DYNAMICS_1054][options][searchById]", "Y");
    formData.append("data[entityTypes][DYNAMICS_1054][options][addTab]", "N");
    formData.append("data[entityTypes][DYNAMICS_1054][options][typeId]", "1054");
    formData.append("data[entityTypes][DYNAMICS_1054][options][onlyWithEmail]", "N");
    formData.append("data[entityTypes][DYNAMICS_1054][options][prefixType]", "SHORT");
    formData.append("data[entityTypes][DYNAMICS_1054][options][returnItemUrl]", "Y");
    formData.append("data[entityTypes][DYNAMICS_1054][options][title]", "Гараж");
    
    // ============ ЭКСПЕРИМЕНТАЛЬНЫЙ ФИЛЬТР ============
    // Вариант 1: стандартный фильтр
    const filterKey1 = `data[FILTER][${ENTITY_CODE}][=CONTACT_ID]`;
    formData.append(filterKey1, contactId);
    
    // Вариант 2: через options (если вариант 1 не работает)
    formData.append("data[options][filter][DYNAMICS_1054][=CONTACT_ID]", contactId);
    
    // Вариант 3: просто CONTACT_ID без =
    formData.append(`data[FILTER][${ENTITY_CODE}][CONTACT_ID]`, contactId);
    
    console.log("DealCarFilter: Отправка запроса с фильтром CONTACT_ID=" + contactId);

    // ВРЕМЕННО: добавляем параметр для отладки
    formData.append("debug", "1");

    fetch("/bitrix/services/main/ajax.php", {
        method: "POST",
        body: formData,
        headers: {
            "X-Requested-With": "XMLHttpRequest",
        },
    })
        .then((response) => response.json())
        .then((data) => {
            console.log("DealCarFilter: Полный ответ сервера:", data);

            if (data.status === "success") {
                const cars = data.data?.ENTITIES?.[ENTITY_CODE]?.ITEMS || {};
                const carCount = Object.keys(cars).length;
                
                console.log("DealCarFilter: Найдено автомобилей:", carCount);
                
                if (carCount === 0) {
                    console.warn("DealCarFilter: Фильтр не сработал или у контакта нет авто");
                    alert("Для выбранного контакта нет автомобилей.\nПопробуйте выбрать авто без фильтра.");
                    
                    // Временно отключаем наш обработчик для одного клика
                    const button = document.querySelector('[data-role="tile-select"]');
                    if (button) {
                        button.removeEventListener("click", handleCarSelectClick, true);
                        setTimeout(() => button.click(), 50);
                        setTimeout(() => {
                            button.addEventListener("click", handleCarSelectClick, true);
                        }, 100);
                    }
                } else {
                    showCarSelection(data.data);
                }
            } else {
                console.error("DealCarFilter: Ошибка сервера:", data.errors);
                alert("Ошибка загрузки автомобилей. Пробуем стандартный выбор.");
                
                // Временно отключаем наш обработчик
                const button = document.querySelector('[data-role="tile-select"]');
                if (button) {
                    button.removeEventListener("click", handleCarSelectClick, true);
                    setTimeout(() => button.click(), 50);
                    setTimeout(() => {
                        button.addEventListener("click", handleCarSelectClick, true);
                    }, 100);
                }
            }
        })
        .catch((error) => {
            console.error("DealCarFilter: Ошибка сети:", error);
            alert("Ошибка сети. Пробуем стандартный выбор.");
            
            const button = document.querySelector('[data-role="tile-select"]');
            if (button) {
                button.removeEventListener("click", handleCarSelectClick, true);
                setTimeout(() => button.click(), 50);
                setTimeout(() => {
                    button.addEventListener("click", handleCarSelectClick, true);
                }, 100);
            }
        });
}



  /**
   * Загрузка всех автомобилей (без фильтра)
   */
  function loadAllCars() {
    console.log("DealCarFilter: Загружаем все автомобили");

    // Создаем стандартный попап Битрикс
    const button = document.querySelector('[data-role="tile-select"]');
    if (button) {
      button.click(); // Инициируем стандартное поведение
    }
  }

  /**
   * Показать выбор автомобилей
   */
  function showCarSelection(responseData) {
    console.log("DealCarFilter: Показываем выбор автомобилей");

    // Проверяем данные
    if (
      !responseData ||
      !responseData.ENTITIES ||
      !responseData.ENTITIES[ENTITY_CODE]
    ) {
      console.warn("DealCarFilter: Нет данных об автомобилях");
      alert("Для выбранного контакта нет автомобилей");
      return;
    }

    const carData = responseData.ENTITIES[ENTITY_CODE];
    const cars = carData.ITEMS || {};

    if (Object.keys(cars).length === 0) {
      alert("Для выбранного контакта нет автомобилей");
      return;
    }

    // Создаем простой попап для выбора
    const popup = document.createElement("div");
    popup.id = "deal-car-popup";
    popup.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border: 2px solid #2a72cc;
            border-radius: 5px;
            padding: 20px;
            z-index: 10000;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
            max-width: 500px;
            max-height: 400px;
            overflow-y: auto;
        `;

    let html = `<h3 style="margin-top:0;color:#2a72cc;">Выберите автомобиль</h3>`;
    html += `<div style="margin-bottom:15px;color:#666;">Для контакта ID: ${currentContactId}</div>`;
    html += `<div id="car-list" style="margin-bottom:15px;">`;

    Object.values(cars).forEach((car) => {
      html += `
                <div style="padding:8px;border-bottom:1px solid #eee;cursor:pointer;"
                     onclick="window.DealCarFilter.selectCar('${car.entityId}', '${car.name.replace(/'/g, "\\'")}')">
                    <strong>${car.name}</strong>
                </div>
            `;
    });

    html += `</div>`;
    html += `<button onclick="document.getElementById('deal-car-popup').remove()" 
                         style="padding:8px 15px;background:#2a72cc;color:white;border:none;border-radius:3px;">
                    Закрыть
                 </button>`;

    popup.innerHTML = html;
    document.body.appendChild(popup);
  }

  /**
   * Выбрать автомобиль
   */
  function selectCar(carId, carName) {
    console.log("DealCarFilter: Выбор автомобиля", carId, carName);

    // Заполняем поле
    const field = document.getElementById(CAR_FIELD_ID);
    if (field) field.value = carId;

    // Обновляем UI
    const tileContainer = document.querySelector(
      '[data-role="tile-container"]',
    );
    if (tileContainer) {
      // Очищаем старые плитки
      const oldTiles = tileContainer.querySelectorAll(
        '[data-role="tile-item"]',
      );
      oldTiles.forEach((tile) => tile.remove());

      // Добавляем новую
      const tile = document.createElement("span");
      tile.setAttribute("data-role", "tile-item");
      tile.setAttribute("data-bx-id", "D" + carId);
      tile.className =
        "ui-tile-selector-item ui-tile-selector-item-dynamic_1054";
      tile.innerHTML = `
                <span data-role="tile-item-name">${carName}</span>
                <span data-role="remove" class="ui-tile-selector-item-remove"></span>
            `;
      tileContainer.insertBefore(tile, tileContainer.firstChild);
    }

    // Закрываем попап
    const popup = document.getElementById("deal-car-popup");
    if (popup) popup.remove();
  }

  /**
   * Настроить обработчик кнопки
   */
  function setupButtonHandler() {
    // Находим кнопку "выбрать"
    const button = document.querySelector('[data-role="tile-select"]');
    if (!button) {
      console.log("DealCarFilter: Кнопка не найдена, повтор через 1 сек");
      setTimeout(setupButtonHandler, 1000);
      return;
    }

    console.log("DealCarFilter: Кнопка найдена, настраиваем обработчик");

    // Удаляем старые обработчики (если есть)
    const newButton = button.cloneNode(true);
    button.parentNode.replaceChild(newButton, button);

    // Вешаем наш обработчик
    newButton.addEventListener("click", handleCarSelectClick, true);

    console.log("DealCarFilter: Обработчик установлен");
  }

  /**
   * Простая проверка контакта каждую секунду
   */
  function startContactChecker() {
    setInterval(() => {
      const contactId = findContactId();
      if (contactId && contactId !== currentContactId) {
        console.log("DealCarFilter: Контакт изменен! ID:", contactId);
        currentContactId = contactId;
      }
    }, 1000);
  }

  /**
   * Инициализация
   */
  function init() {
    if (isInitialized) return;

    console.log("DealCarFilter: Инициализация упрощенной версии");

    // 1. Настраиваем кнопку
    setTimeout(setupButtonHandler, 1500);

    // 2. Запускаем проверку контакта
    startContactChecker();

    // 3. Публичные методы
    window.DealCarFilter = {
      getContact: () => currentContactId,

      selectCar: function (carId, carName) {
        selectCar(carId, carName);
      },

      debug: function () {
        console.log("=== DealCarFilter Debug ===");
        console.log("Контакт:", currentContactId);
        console.log(
          "Кнопка:",
          document.querySelector('[data-role="tile-select"]'),
        );
        console.log("Поле автомобиля:", document.getElementById(CAR_FIELD_ID));
      },
    };

    isInitialized = true;
    console.log("DealCarFilter: Инициализация завершена");
  }

  // ==================== ЗАПУСК ====================

  // Ждем загрузки
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
