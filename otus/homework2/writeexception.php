<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<h3>В лог добавлена информация по ошибке 1/0: <a href='/logs/exception_<?php echo date('Y-m-d'); ?>.log' target="_blank">Посмотреть лог ловца</a></h3>

<?php  $a = 1/0; ?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?> 
