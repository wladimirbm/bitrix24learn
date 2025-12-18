<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php 
    $APPLICATION->SetTitle("Домашняя работа 7 (Создание кастомных полей и встраивание их в систему // ДЗ)");
?>
<H1><?$APPLICATION->ShowTitle()?></H1>

<h2>Создание кастомных полей и встраивание их в систему</h2>

<p><a href="/services/lists/16/view/0/">Список Докторов</a></p>
<p><a href="/services/lists/21/view/0/">Список Бронирований</a></p>

<h2>Файлы</h2>

<p><a href="/bitrix/admin/fileman_admin.php?lang=ru&path=%2Flocal%2Fphp_interface%2Fclasses%2FUserTypes&site=s1">Файл класса пользовательского типа</a></p>
<p><a href="/bitrix/admin/fileman_admin.php?lang=ru&path=%2Flocal%2Fjs%2Fotus%2Fbooking&site=s1">Файлы js логики окна</a></p>
<p><a href="/bitrix/admin/fileman_admin.php?lang=ru&path=%2Flocal%2Fajax&site=s1">Файлы ajax обработки</a></p>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?> 
