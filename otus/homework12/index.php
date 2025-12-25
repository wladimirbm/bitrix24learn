<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Домашняя работа 11 (Локальные приложения и вебхуки // ДЗ )");
?>
<H1><? $APPLICATION->ShowTitle() ?></H1>

<h2>Локальные приложения и вебхуки</h2>

<p><a href="/crm/contact/list/">Контакты</a></p>


<h2>Файлы</h2>
<p><a href="/bitrix/admin/fileman_admin.php?lang=ru&path=%2Flocal%2Fapp%2Fwebhook&site=s1">Обработчик исходящего вебхука</a></p>


<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?> 