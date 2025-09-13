<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php 
    $APPLICATION->SetTitle("Домашняя работа 2 (Отладка и логирование // ДЗ)");
?>
<H1><?$APPLICATION->ShowTitle()?></H1>
<h2>Вывод отладочной информации</h2>
<?php
    dump($_SERVER);
    echo "<hr><pre>";
    print_r($_SERVER);
    echo "</pre>";
?>
<h2>Часть 1</h2>
<p>Файл лога</p>
<p>Добавление в лог</p>
<p>Файл с классом кастомного логера</p>
<h2>Часть 2</h2>
<p>Файл лога</p>
<p>Добавление в лог</p>
<p>Файл с классом кастомного исключения</p>

<?php 
Bitrix\Main\Diag\Debug::writeToFile($_SERVER, $varName = '$_SERVER', $fileName ='');
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
