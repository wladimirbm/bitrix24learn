<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php 
    $APPLICATION->SetTitle("Домашняя работа 6 (Написание своего модуля // ДЗ)");
?>
<H1><?$APPLICATION->ShowTitle()?></H1>

<?php 
echo str_pad(RAND(1, 29), 2, "0", STR_PAD_LEFT) . '.' . str_pad(RAND(1, 12), 2, "0", STR_PAD_LEFT) . '.' . RAND(1960, 2000);
?>

<h2>Написание своего модуля</h2>
<p><a href="/crm/deal/list/">Сделки</a></p>
<p><a href="/crm/lead/list/">Лиды</a></p>
<p><a href="/crm/contact/list/">Контакты</a></p>
<p><a href="/crm/company/list/">Компании</a></p>

<h2>Файлы</h2>

<p><a href="/bitrix/admin/partner_modules.php?lang=ru">Установка/Удаление</a></p>
<p><a href="/bitrix/admin/settings.php?mid=otus.crmcustomtab&lang=ru">Настройки</a></p>
<p><a href="/bitrix/admin/perfmon_tables.php?lang=ru#mod_">Таблицы</a></p>
<p><a href="/bitrix/admin/fileman_admin.php?lang=ru&path=%2Flocal%2Fmodules%2Fotus.crmcustomtab&site=s1">Файлы модуля</a></p>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?> 
