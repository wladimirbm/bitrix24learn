<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<h3>В лог добавлена информация: <a href='/logs/mylog_<?php echo date('Y-m-d'); ?>.log' target="_blank">Посмотреть лог файл</a></h3>
<pre>
    <?php print_r($_SERVER); ?>
</pre>
<?php
App\Diag\Mylog::addLog($_SERVER, '$_SERVER', '', __FILE__, __LINE__);
?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>