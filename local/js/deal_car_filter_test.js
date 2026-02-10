// ПРОСТОЙ ТЕСТОВЫЙ СКРИПТ
console.log('Car Filter: Скрипт загружен!');

// Ждем загрузки страницы
setTimeout(function() {
    console.log('Ищем поля...');
    
    // 1. Поиск поля контакта
    var contactField = null;
    
    // Способ 1: По data-cid
    contactField = document.querySelector('[data-cid="CLIENT"]');
    if (contactField) console.log('Поле CLIENT найдено (data-cid)');
    
    // Способ 2: По классу
    if (!contactField) {
        contactField = document.querySelector('.crm-entity-widget-client-block');
        if (contactField) console.log('Поле CLIENT найдено (по классу)');
    }
    
    // Способ 3: По ссылке на контакт
    if (!contactField) {
        var contactLink = document.querySelector('a[href*="/crm/contact/details/"]');
        if (contactLink) {
            console.log('Контакт найден по ссылке:', contactLink.href);
            contactField = contactLink.closest('[data-cid="CLIENT"], .crm-entity-widget-content-block');
        }
    }
    
    if (!contactField) {
        console.log('Поле контакта не найдено!');
        console.log('Все элементы с CLIENT:', document.querySelectorAll('[id*="CLIENT"], [name*="CLIENT"]').length);
        return;
    }
    
    // 2. Поиск поля автомобиля
    var carField = document.querySelector('[id*="uf-crm-1770716463"]');
    if (!carField) {
        console.log('Поле авто не найдено!');
        console.log('Поиск по data-field:', document.querySelector('[data-field="UF_CRM_1770716463"]'));
        return;
    }
    
    console.log('Поле авто найдено:', carField.id);
    
    // 3. Проверка BX объектов
    console.log('BX:', typeof BX);
    console.log('BX.Crm:', BX && BX.Crm ? 'есть' : 'нет');
    console.log('BX.UI:', BX && BX.UI ? 'есть' : 'нет');
    
    // 4. Простая логика: при клике на поле авто выводим контакт
    carField.addEventListener('click', function() {
        console.log('Клик на поле авто!');
        var contactId = getContactId();
        console.log('ID контакта:', contactId);
    });
    
    // Функция получения ID контакта
    function getContactId() {
        var contactLink = document.querySelector('a[href*="/crm/contact/details/"]');
        if (contactLink) {
            var match = contactLink.href.match(/\/crm\/contact\/details\/(\d+)/);
            return match ? match[1] : null;
        }
        return null;
    }
    
    console.log('Инициализация завершена!');
    
}, 3000); // Даем 3 секунды на загрузку

console.log('Car Filter: Запущен таймер инициализации');