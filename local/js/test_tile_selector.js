// Тестовый скрипт для поиска tile selector
console.log('=== ТЕСТ ПОИСКА TILE SELECTOR ===');

// Функция для поиска и вывода информации
function findTileSelectors() {
    console.log('1. Все элементы с 1770716463:');
    const elementsWithId = [];
    
    // Ищем по всем элементам
    const allElements = document.querySelectorAll('*');
    allElements.forEach(el => {
        const id = el.id || '';
        const name = el.getAttribute('name') || '';
        const dataField = el.getAttribute('data-field') || '';
        
        if (id.includes('1770716463') || 
            name.includes('1770716463') || 
            dataField.includes('1770716463')) {
            elementsWithId.push({
                element: el,
                id: id,
                name: name,
                'data-field': dataField,
                tag: el.tagName,
                class: el.className.substring(0, 50)
            });
        }
    });
    
    if (elementsWithId.length > 0) {
        console.table(elementsWithId);
    } else {
        console.log('Не найдено элементов с 1770716463');
    }
    
    console.log('2. Все .ui-tile-selector-selector-wrap:');
    const tileSelectors = document.querySelectorAll('.ui-tile-selector-selector-wrap');
    console.log('Найдено:', tileSelectors.length);
    
    tileSelectors.forEach((sel, i) => {
        console.log(`Tile selector ${i}:`);
        console.log('  ID:', sel.id);
        console.log('  Class:', sel.className);
        console.log('  HTML:', sel.outerHTML.substring(0, 200) + '...');
        
        // Ищем input внутри
        const input = sel.querySelector('input');
        if (input) {
            console.log('  Input ID:', input.id);
            console.log('  Input name:', input.name);
            console.log('  Input value:', input.value);
        }
    });
    
    console.log('3. Все input с 1770716463:');
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        const id = input.id || '';
        const name = input.name || '';
        
        if (id.includes('1770716463') || name.includes('1770716463')) {
            console.log('Input найдено:', {
                id: id,
                name: name,
                type: input.type,
                value: input.value,
                parentId: input.parentNode ? input.parentNode.id : 'нет'
            });
        }
    });
    
    console.log('4. Все скрытые input (hidden):');
    const hiddenInputs = document.querySelectorAll('input[type="hidden"]');
    hiddenInputs.forEach(input => {
        if (input.name.includes('CRM') || input.id.includes('CRM')) {
            console.log('Hidden:', input.name, '=', input.value);
        }
    });
    
    console.log('=== ТЕСТ ЗАВЕРШЕН ===');
}

// Запускаем поиск через 3 секунды после загрузки
setTimeout(findTileSelectors, 3000);

// Также запускаем при клике на документ (чтобы поймать динамически созданные элементы)
document.addEventListener('click', function() {
    setTimeout(function() {
        console.log('=== ПРОВЕРКА ПОСЛЕ КЛИКА ===');
        findTileSelectors();
    }, 500);
});