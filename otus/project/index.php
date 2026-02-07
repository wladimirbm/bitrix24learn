<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle('Проектная работа "Внедрение Битрикс24 в дилерский салон"');
?>
<H1><? $APPLICATION->ShowTitle() ?></H1>



<h3>Сотрудники</h3>
<p><a href="/company/">Список</a></p>
<p><a href="/hr/structure/">Структура</a></p>
<h3>Права доступа</h3>
<p><a href="/crm/perms/all/">Таблица</a></p>
<h3>Смарт процессы</h3>
<p><a href="/crm/type/">Список</a></p>
<h3>Каталог товаров</h3>
<p><a href="/crm/catalog/list/16/?IBLOCK_ID=14&type=CRM_PRODUCT_CATALOG&lang=ru&find_section_section=16&SECTION_ID=16&apply_filter=Y">Запчасти</a></p>
<h3>Агент</h3>
<p><a href="/bitrix/admin/fileman_admin.php?lang=ru&path=%2Flocal%2Fphp_interface%2Fagents&site=s1">Код</a></p>
<p><a href="/bitrix/admin/perfmon_table.php?lang=ru&table_name=b_agent&apply_filter=Y&by=ID&order=desc">Таблица</a></p>
<h3>Триггеры</h3>
<p><a href="/">Код</a></p>


<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?> 