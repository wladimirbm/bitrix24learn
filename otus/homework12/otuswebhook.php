<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/local/app/webhook/crest.php');
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Rest запросы:");
?>
<H1><? $APPLICATION->ShowTitle() ?></H1>

<?php

if (!function_exists('dump')) {
    function dump($var)
    {
        echo '<pre>';
        var_dump($var);
        echo '</pre><hr>';
    }
}

echo '<h1>Тестирование REST otus.doctor</h1>';

// ================== LIST ==================
echo '<h3>1. otus.doctor.list (первые 5)</h3>';
dump(CRest::call('otus.doctor.list', ['limit' => 5]));

// ================== ADD ==================
$timestamp = time();
$uniqueLastName = 'Тестовый_' . $timestamp;

echo "<h3>2. otus.doctor.add</h3>";
echo "<em>Уникальная фамилия: {$uniqueLastName}</em><br>";

$addResult = CRest::call('otus.doctor.add', [
    'LASTNAME' => $uniqueLastName,
    'FIRSTNAME' => 'Доктор_' . date('d-m-Y H:i:s'),
    'MIDDLENAME' => 'Тест',
    'DUTY_ID' => rand(1, 100),
    'BIRTHDAY' =>  str_pad(RAND(1, 29), 2, "0", STR_PAD_LEFT) . '.' . str_pad(RAND(1, 12), 2, "0", STR_PAD_LEFT) . '.' . RAND(1960, 2000),
    //'BIRTHDAY' => RAND(1960, 2000) . '-' . str_pad(RAND(1, 12), 2, "0", STR_PAD_LEFT) . '-' . str_pad(RAND(1, 29), 2, "0", STR_PAD_LEFT),
]);

dump($addResult);

$newDoctorId = $addResult['result']['ID'] ?? 0;

// ================== GET ==================
if ($newDoctorId) {
    echo "<h3>3. otus.doctor.get (ID={$newDoctorId})</h3>";
    dump(CRest::call('otus.doctor.get', ['ID' => $newDoctorId]));
}

// ================== UPDATE ==================
if ($newDoctorId) {
    echo "<h3>4. otus.doctor.update (ID={$newDoctorId})</h3>";
    dump(CRest::call('otus.doctor.update', [
        'ID' => $newDoctorId,
        'LASTNAME' => $uniqueLastName . '_обновлено'
    ]));
}

// ================== DELETE ==================
if ($newDoctorId) {
    echo "<h3>5. otus.doctor.delete (ID={$newDoctorId})</h3>";
    dump(CRest::call('otus.doctor.delete', ['ID' => $newDoctorId]));
}

// ================== ВЕБХУК ADD ==================
// echo '<h3>6. Входящий вебхук onOtusDoctorAdd</h3>';
// dump(CRest::call('onOtusDoctorAdd', [
//     'data' => [
//         'LASTNAME' => 'Вебхук_' . time(),
//         'FIRSTNAME' => 'Доктор'
//     ]
// ]));

echo '<h2>✅ Тестирование завершено</h2>';
