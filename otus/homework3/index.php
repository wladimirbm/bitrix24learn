<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php 
    $APPLICATION->SetTitle("Домашняя работа 3 (Связывание моделей // ДЗ)");
?>
<H1><?$APPLICATION->ShowTitle()?></H1>
<?php
    /*
    dump($_SERVER);
    echo "<hr><pre>";
    print_r($_SERVER);
    echo "</pre>";
    */
?>
<h2>Список</h2>
<p><a href="/logs/mylog_<?php echo date('Y-m-d');?>.log">Файл лога</a></p>

<h2>Модель</h2>
<p><a href="/logs/mylog_<?php echo date('Y-m-d');?>.log">Файл лога</a></p>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?> 
