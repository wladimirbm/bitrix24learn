<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Домашняя работа 10 (Обработка событий // ДЗ)");
?>
<H1><? $APPLICATION->ShowTitle() ?></H1>

<h2>Обработчик изменений в элементе инфоблока</h2>

<p><a href="/services/lists/20/view/0/">Инфоблок</a></p>


<h2>Файлы</h2>
<p><a href="/bitrix/admin/fileman_admin.php?lang=ru&path=%2Flocal%2Fapp%2Fevents&site=s1">Классы обработчика</a></p>
<p><a href="/bitrix/admin/fileman_admin.php?lang=ru&path=%2Flocal%2Fphp_interface&site=s1">Подключение обработчика events.php</a></p>


<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?> 