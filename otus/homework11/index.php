<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Домашняя работа 11 (Локальные приложения и вебхуки // ДЗ )");
?>
<H1><? $APPLICATION->ShowTitle() ?></H1>

<h2>Локальные приложения и вебхуки</h2>

<p><a href="/services/lists/20/view/0/">Вебхук</a></p>


<h2>Файлы</h2>
<p><a href="/bitrix/admin/fileman_admin.php?lang=ru&path=%2Flocal%2Fapp%2Fevents&site=s1">Классы обработчика</a></p>
<p><a href="/bitrix/admin/fileman_admin.php?lang=ru&path=%2Flocal%2Fphp_interface&site=s1">Подключение обработчика events.php</a></p>


<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?> 