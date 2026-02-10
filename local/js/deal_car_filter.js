(function () {
  "use strict";

  const SMART_PROCESS_TYPE_ID = 1054;
  const ENTITY_CODE = "DYNAMICS_" + SMART_PROCESS_TYPE_ID;
  const CAR_FIELD_ID = "UF_CRM_1770588718";

  let currentContactId = null;
  let isInitialized = false;
  let isProcessing = false;

  console.log("DealCarFilter: Загружен");

  function findContactId() {
    const elements = document.querySelectorAll("[data-cid]");

    for (let element of elements) {
      const dataCid = element.getAttribute("data-cid");
      if (dataCid && dataCid.startsWith("CONTACT_")) {
        const match = dataCid.match(/CONTACT_(\d+)_/);
        if (match && match[1]) {
          return match[1];
        }
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

    setTimeout(() => {
      isProcessing = false;
    }, 1000);

    return false;
  }

  function loadFilteredCars(contactId) {
    const csrfToken =
      document.querySelector('meta[name="csrf-token"]')?.content ||
      document.querySelector('input[name="sessid"]')?.value;

    if (!csrfToken) return;

    const params = new URLSearchParams();
    params.append("mode", "ajax");
    params.append("c", "bitrix:main.ui.selector");
    params.append("action", "getData");
    params.append("sessid", csrfToken);

    const dataParams = {
      "options[useNewCallback]": "Y",
      "options[eventInit]": "BX.Main.User.SelectorController::init",
      "options[eventOpen]": "BX.Main.User.SelectorController::open",
      "options[lazyLoad]": "Y",
      "options[context]": "crmEntityCreate",
      "options[enableCrm]": "Y",
      "options[crmPrefixType]": "SHORT",
      "options[enableCrmDynamics][1054]": "Y",
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
      [`FILTER[${ENTITY_CODE}][=CONTACT_ID]`]: contactId,
    };

    for (const [key, value] of Object.entries(dataParams)) {
      params.append(`data[${key}]`, value);
    }

    fetch("/bitrix/services/main/ajax.php", {
      method: "POST",
      body: params,
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.status === "success") {
          const cars = data.data?.ENTITIES?.[ENTITY_CODE]?.ITEMS || {};
          if (Object.keys(cars).length === 0) {
            fallbackToStandard();
          } else {
            showCarSelection(data.data);
          }
        } else {
          fallbackToStandard();
        }
      })
      .catch((error) => {
        console.error("DealCarFilter: Ошибка:", error);
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
      }, 100);
    }
  }

  function showCarSelection(responseData) {
    if (
      !responseData ||
      !responseData.ENTITIES ||
      !responseData.ENTITIES[ENTITY_CODE]
    ) {
      alert("Для выбранного контакта нет автомобилей");
      return;
    }

    const carData = responseData.ENTITIES[ENTITY_CODE];
    const cars = carData.ITEMS || {};

    if (Object.keys(cars).length === 0) {
      alert("Для выбранного контакта нет автомобилей");
      return;
    }

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

  function selectCar(carId, carName) {
    const field = document.getElementById(CAR_FIELD_ID);
    if (field) field.value = carId;

    const tileContainer = document.querySelector(
      '[data-role="tile-container"]'
    );
    if (tileContainer) {
      const oldTiles = tileContainer.querySelectorAll(
        '[data-role="tile-item"]'
      );
      oldTiles.forEach((tile) => tile.remove());

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

    const popup = document.getElementById("deal-car-popup");
    if (popup) popup.remove();
  }

  function setupButtonHandler() {
    const button = document.querySelector('[data-role="tile-select"]');
    if (!button) {
      setTimeout(setupButtonHandler, 1000);
      return;
    }

    const newButton = button.cloneNode(true);
    button.parentNode.replaceChild(newButton, button);
    newButton.addEventListener("click", handleCarSelectClick, true);
  }

  function startContactChecker() {
    setInterval(() => {
      const contactId = findContactId();
      if (contactId && contactId !== currentContactId) {
        currentContactId = contactId;
      }
    }, 1000);
  }

  function init() {
    if (isInitialized) return;

    setTimeout(setupButtonHandler, 1500);
    startContactChecker();

    window.DealCarFilter = {
      getContact: () => currentContactId,
      selectCar: function (carId, carName) {
        selectCar(carId, carName);
      },
    };

    isInitialized = true;
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();