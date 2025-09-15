<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php 
    $APPLICATION->SetTitle("Домашняя работа 2 (Отладка и логирование // ДЗ)");
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
<h2>Часть 1 - Logger</h2>
<p><a href="/logs/mylog_<?php echo date('Y-m-d');?>.log">Файл лога</a></p>
<p><a href="clearlog.php">Очистить лог</a></p>
<p><a href="writelog.php" target="_blank">Добавление в лог</a></p>
<p><a href="/bitrix/admin/fileman_admin.php?lang=ru&site=s1&path=%2Flocal%2Fapp%2Fdebug" target="_blank">Файл с классом кастомного логера</a></p>

<h2>Часть 2 - Exception</h2>
<p><a href="/logs/exceptioin_<?php echo date('Y-m-d');?>.log">Файл лога</a></p>
<p><a href="clearexception.php" target="_blank">Очистить лог</a></p>
<p><a href="writeexception.php" target="_blank">Добавление в лог</a></p>
<p><a href="/bitrix/admin/fileman_admin.php?PAGEN_1=1&SIZEN_1=20&lang=ru&site=s1&path=%2Flocal%2Fphp_interface%2Fsrc%2FDiag&show_perms_for=0&fu_action=" target="_blank">Файл с классом кастомного логера</a></p>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?> 
