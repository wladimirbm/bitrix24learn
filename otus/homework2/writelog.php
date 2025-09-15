<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
\Bitrix\Main\Diag\Debug::dumpToFile($_SERVER, '$_SERVER');
\Bitrix\Main\Diag\Debug::writeToFile($_SERVER, '$_SERVER');
\App\Debug\Mylog::addLog($_SERVER, '$_SERVER', '', __FILE__, __LINE__);
echo "\App\Debug\Mylog::addLog(_SERVER, '_SERVER', '', ".__FILE__.", ".__LINE__.");";
?>
<h3>В лог добавлена информация: <a href='/logs/mylog_<?php echo date('Y-m-d'); ?>.log' target="_blank">Посмотреть лог файл</a></h3>
<pre>
    <?php print_r($_SERVER); ?>
</pre>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?> 