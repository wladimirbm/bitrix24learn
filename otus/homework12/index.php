<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Домашняя работа 12 (Добавление собственных методов REST // ДЗ)");
?>
<H1><? $APPLICATION->ShowTitle() ?></H1>

<h2>Добавление собственных методов REST</h2>

<p><a href="otuswebhook.php">Выполнение Rest запросов</a></p>
<p><a href="otuswebhook.php?nodelete">Выполнение Rest запросов - без удаления тестового</a></p>


<h2>Файлы</h2>
<p><a href="/bitrix/admin/fileman_admin.php?lang=ru&path=%2Flocal%2Fphp_interface%2Fsrc%2FRest&site=s1">Обработчик вебхуков</a></p>
<p><a href="/devops/edit/in-hook/3/">Входящий вебхук для тестирования</a></p>


<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?> 