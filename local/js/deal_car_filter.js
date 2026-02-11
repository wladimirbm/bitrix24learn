(function () {
  if (window.DealCarFilterLoaded) return;
  window.DealCarFilterLoaded = true;

  const SMART_PROCESS_TYPE_ID = 1054;
  const ENTITY_CODE = "DYNAMICS_" + SMART_PROCESS_TYPE_ID;
  const CAR_FIELD_ID = "UF_CRM_1770588718";

  let currentContactId = null;
  let isProcessing = false;

  function findContactId() {
    const elements = document.querySelectorAll("[data-cid]");
    for (let element of elements) {
      const dataCid = element.getAttribute("data-cid");
      if (dataCid && dataCid.startsWith("CONTACT_")) {
        const match = dataCid.match(/CONTACT_(\d+)_/);
        if (match && match[1]) return match[1];
      }
    }
    return null;
  }

  function handleCarSelectClick(event) {
    if (isProcessing) return;
    isProcessing = true;

    const contactId = findContactId();
    if (!contactId) {
      isProcessing = false;
      return;
    }

    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();

    loadFilteredCars(contactId);

    setTimeout(() => { isProcessing = false; }, 1000);
    return false;
  }

  function loadFilteredCars(contactId) {
    const csrfToken = document.querySelector('input[name="sessid"]')?.value;
    if (!csrfToken) {
      fallbackToStandard();
      return;
    }

    const formData = new FormData();
    
    // ВСЕ параметры как в оригинале (POST данные)
    const params = {
      "options[useNewCallback]": "Y",
      "options[eventInit]": "BX.Main.User.SelectorController::init",
      "options[eventOpen]": "BX.Main.User.SelectorController::open",
      "options[lazyLoad]": "Y",
      "options[context]": "crmEntityCreate",
      "options[contextCode]": "",
      "options[enableSonetgroups]": "N",
      "options[enableUsers]": "N",
      "options[useClientDatabase]": "N",
      "options[enableAll]": "N",
      "options[enableDepartments]": "N",
      "options[enableCrm]": "Y",
      "options[crmPrefixType]": "SHORT",
      "options[enableCrmDynamics][1054]": "Y",
      "options[addTabCrmDynamics][1054]": "N",
      "options[addTabCrmContacts]": "N",
      "options[addTabCrmCompanies]": "N",
      "options[addTabCrmLeads]": "N",
      "options[addTabCrmDeals]": "N",
      "options[addTabCrmOrders]": "N",
      "options[addTabCrmQuotes]": "N",
      "options[addTabCrmSmartInvoices]": "N",
      "options[crmDynamicTitles][DYNAMICS_1040]": "Марка автомобиля",
      "options[crmDynamicTitles][DYNAMICS_1046]": "Модель автомобиля",
      "options[crmDynamicTitles][DYNAMICS_1054]": "Гараж",
      "options[crmDynamicTitles][DYNAMICS_1058]": "Заявка на закупку",
      "options[multiple]": "N",
      "options[extranetContext]": "false",
      "options[useSearch]": "N",
      "options[userNameTemplate]": "#NAME# #LAST_NAME#",
      "options[allowEmailInvitation]": "N",
      "options[departmentSelectDisable]": "Y",
      "options[allowAddUser]": "N",
      "options[allowAddCrmContact]": "N",
      "options[allowAddSocNetGroup]": "N",
      "options[allowSearchEmailUsers]": "N",
      "options[allowSearchCrmEmailUsers]": "N",
      "options[allowSearchNetworkUsers]": "N",
      "entityTypes[GROUPS][options][context]": "crmEntityCreate",
      "entityTypes[GROUPS][options][enableAll]": "N",
      "entityTypes[GROUPS][options][enableEmpty]": "N",
      "entityTypes[GROUPS][options][enableUserManager]": "N",
      "entityTypes[EMAILUSERS][options][allowAdd]": "N",
      "entityTypes[EMAILUSERS][options][allowAddCrmContact]": "N",
      "entityTypes[EMAILUSERS][options][allowSearchCrmEmailUsers]": "N",
      "entityTypes[EMAILUSERS][options][addTab]": "N",
      "entityTypes[DYNAMICS_1054][options][enableSearch]": "Y",
      "entityTypes[DYNAMICS_1054][options][searchById]": "Y",
      "entityTypes[DYNAMICS_1054][options][addTab]": "N",
      "entityTypes[DYNAMICS_1054][options][typeId]": "1054",
      "entityTypes[DYNAMICS_1054][options][onlyWithEmail]": "N",
      "entityTypes[DYNAMICS_1054][options][prefixType]": "SHORT",
      "entityTypes[DYNAMICS_1054][options][returnItemUrl]": "Y",
      "entityTypes[DYNAMICS_1054][options][title]": "Гараж",
      "sessid": csrfToken,
    };

    // Добавляем ВСЕ параметры в FormData
    for (const [key, value] of Object.entries(params)) {
      formData.append(key, value);
    }

    // Пробуем добавить фильтр
    formData.append("options[contextEntityType]", "CONTACT");
    formData.append("options[contextEntityId]", contactId);
    formData.append("FILTER[DYNAMICS_1054][CONTACT_ID]", contactId);
    formData.append("FILTER[DYNAMICS_1054][=CONTACT_ID]", contactId);
    formData.append("entityTypes[DYNAMICS_1054][options][filter][CONTACT_ID]", contactId);
    formData.append("entityTypes[DYNAMICS_1054][options][CONTACT_ID]", contactId);
    formData.append("entityTypes[DYNAMICS_1054][options][ParentID ]", contactId);

    console.log("Запрос с контактом:", contactId);

    // ВАЖНО: GET-параметры в URL, POST-данные в body
    fetch(`/bitrix/services/main/ajax.php?mode=ajax&c=bitrix%3Amain.ui.selector&action=getData`, {
      method: "POST",
      body: formData,
      headers: { "X-Requested-With": "XMLHttpRequest" },
    })
      .then(response => response.json())
      .then(data => {
        console.log("Ответ:", data);
        
        if (data.status === "success") {
          const cars = data.data?.ENTITIES?.[ENTITY_CODE]?.ITEMS || {};
          if (Object.keys(cars).length > 0) {
            showCarSelection(data.data);
          } else {
            console.log("Нет авто для контакта, пробуем стандартный");
            fallbackToStandard();
          }
        } else {
          console.error("Ошибка:", data.errors);
          fallbackToStandard();
        }
      })
      .catch(error => {
        console.error("Ошибка сети:", error);
        fallbackToStandard();
      });
  }

  function fallbackToStandard() {
    const button = document.querySelector('[data-role="tile-select"]');
    if (button) {
      button.removeEventListener("click", handleCarSelectClick, true);
      setTimeout(() => button.click(), 50);
      setTimeout(() => {
        button.addEventListener("click", handleCarSelectClick, true);
      }, 500);
    }
  }

  function showCarSelection(responseData) {
    if (!responseData?.ENTITIES?.[ENTITY_CODE]?.ITEMS) return;

    const cars = responseData.ENTITIES[ENTITY_CODE].ITEMS;
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
    html += `<div id="car-list">`;

    Object.values(cars).forEach(car => {
      html += `
        <div style="padding:8px;border-bottom:1px solid #eee;cursor:pointer;"
             onclick="window.DealCarFilter.selectCar('${car.entityId}', '${car.name.replace(/'/g, "\\'")}')">
          <strong>${car.name}</strong>
        </div>
      `;
    });

    html += `</div>
      <button onclick="document.getElementById('deal-car-popup').remove()" 
              style="margin-top:15px;padding:8px 15px;background:#2a72cc;color:white;border:none;border-radius:3px;">
        Закрыть
      </button>`;

    popup.innerHTML = html;
    document.body.appendChild(popup);
  }

  function selectCar(carId, carName) {
    const field = document.getElementById(CAR_FIELD_ID);
    if (field) field.value = carId;

    const tileContainer = document.querySelector('[data-role="tile-container"]');
    if (tileContainer) {
      tileContainer.innerHTML = '';
      const tile = document.createElement("span");
      tile.setAttribute("data-role", "tile-item");
      tile.setAttribute("data-bx-id", "D" + carId);
      tile.className = "ui-tile-selector-item ui-tile-selector-item-dynamic_1054";
      tile.innerHTML = `
        <span data-role="tile-item-name">${carName}</span>
        <span data-role="remove" class="ui-tile-selector-item-remove"></span>
      `;
      tileContainer.appendChild(tile);
    }

    const popup = document.getElementById("deal-car-popup");
    if (popup) popup.remove();
  }

  function setupButtonHandler() {
    const button = document.querySelector('[data-role="tile-select"]');
    if (!button) {
      setTimeout(setupButtonHandler, 500);
      return;
    }
    button.addEventListener("click", handleCarSelectClick, true);
    console.log("Обработчик установлен");
  }

  window.DealCarFilter = {
    getContact: () => currentContactId,
    selectCar: function (carId, carName) {
      selectCar(carId, carName);
    },
  };

  function init() {
    setTimeout(setupButtonHandler, 1000);
    setInterval(() => {
      const contactId = findContactId();
      if (contactId) currentContactId = contactId;
    }, 1000);
    console.log("DealCarFilter инициализирован");
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    setTimeout(init, 500);
  }
})();