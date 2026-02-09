<!DOCTYPE html>
<html>
<head>
    <title>Тест истории авто</title>
    <script src="/bitrix/js/main/core/core.js"></script>
    <script src="/bitrix/js/main/popup/popup.js"></script>
    <script src="/local/js/car_detail.js"></script>
    <style>
        body { padding: 20px; font-family: Arial; }
        .test-link { 
            display: inline-block; 
            padding: 10px 20px; 
            background: #2fc6f6; 
            color: white; 
            border-radius: 5px; 
            margin: 10px; 
            text-decoration: none; 
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h2>Тест модуля истории авто</h2>
    
    <div>
        <a href="/crm/type/1054/details/1/" class="test-link">Audi 100 (1985)</a>
        <a href="/crm/type/1054/details/2/" class="test-link">Opel Astra (1980)</a>
    </div>
    
    <div style="margin-top: 30px; padding: 20px; background: #f5f5f5; border-radius: 5px;">
        <h3>Проверка в консоли:</h3>
        <button onclick="showCarHistory(1)">Показать авто ID=1</button>
        <button onclick="debugCarHistory.intercept()">Перехватить ссылки</button>
        <button onclick="debugCarHistory.removeButtons()">Удалить кнопки</button>
    </div>
    
    <script>
    setTimeout(function() {
        console.log('BX:', typeof BX);
        console.log('showCarHistory:', typeof showCarHistory);
        console.log('debugCarHistory:', debugCarHistory);
    }, 1000);
    </script>
</body>
</html>