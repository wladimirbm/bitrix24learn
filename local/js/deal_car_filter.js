function loadFilteredCars(contactId) {
    const csrfToken = document.querySelector('input[name="sessid"]')?.value;
    if (!csrfToken) {
        fallbackToStandard();
        return;
    }

    const formData = new FormData();
    formData.append("mode", "ajax");
    formData.append("c", "bitrix:main.ui.selector");
    formData.append("action", "getData");
    formData.append("sessid", csrfToken);

    // ВСЕ параметры должны быть внутри data[...]
    const dataParams = {
        // Options
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
        
        // Entity Types
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
        
        // Фильтр по контакту
        "entityTypes[DYNAMICS_1054][options][parentEntityType]": "CONTACT",
        "entityTypes[DYNAMICS_1054][options][parentEntityId]": contactId,
    };

    // Ключевое: Оборачиваем ВСЕ параметры в data[...]
    for (const [key, value] of Object.entries(dataParams)) {
        formData.append(`data[${key}]`, value);
    }

    console.log("Отправка запроса, контакт:", contactId);
    
    // Для отладки: выводим все параметры
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }

    fetch("/bitrix/services/main/ajax.php", {
        method: "POST",
        body: formData,
        headers: { "X-Requested-With": "XMLHttpRequest" },
    })
    .then(response => response.json())
    .then(data => {
        console.log("Ответ сервера:", data);
        
        if (data.status === "success") {
            const cars = data.data?.ENTITIES?.[ENTITY_CODE]?.ITEMS || {};
            if (Object.keys(cars).length > 0) {
                console.log("Найдено авто:", Object.keys(cars).length);
                showCarSelection(data.data);
            } else {
                console.log("Нет авто для контакта");
                
                // Пробуем запрос БЕЗ фильтра для сравнения
                const testFormData = new FormData();
                testFormData.append("mode", "ajax");
                testFormData.append("c", "bitrix:main.ui.selector");
                testFormData.append("action", "getData");
                testFormData.append("sessid", csrfToken);
                
                // Только базовые параметры БЕЗ фильтра
                const basicParams = {
                    "options[useNewCallback]": "Y",
                    "options[context]": "crmEntityCreate",
                    "options[enableCrm]": "Y",
                    "options[crmPrefixType]": "SHORT",
                    "options[enableCrmDynamics][1054]": "Y",
                    "options[multiple]": "N",
                    "entityTypes[DYNAMICS_1054][options][typeId]": "1054",
                    "entityTypes[DYNAMICS_1054][options][enableSearch]": "Y",
                    "entityTypes[DYNAMICS_1054][options][prefixType]": "SHORT",
                    "entityTypes[DYNAMICS_1054][options][title]": "Гараж",
                };
                
                for (const [key, value] of Object.entries(basicParams)) {
                    testFormData.append(`data[${key}]`, value);
                }
                
                fetch("/bitrix/services/main/ajax.php", {
                    method: "POST",
                    body: testFormData,
                    headers: { "X-Requested-With": "XMLHttpRequest" },
                })
                .then(r => r.json())
                .then(testData => {
                    console.log("Тест без фильтра:", testData);
                    if (testData.status === "success") {
                        showCarSelection(testData.data);
                    } else {
                        fallbackToStandard();
                    }
                });
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