<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php 
    $APPLICATION->SetTitle("Домашняя работа 3 (Связывание моделей // ДЗ)");
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
<h2>Создание своих таблиц БД и написание модели данных к ним</h2>
<p><a href="reltable.php">Таблица с зависимостями</a></p>

<h2>Файлы</h2>
 

<p><a href="/bitrix/admin/fileman_admin.php?PAGEN_1=1&SIZEN_1=20&lang=ru&site=s1&path=%2Flocal%2Fphp_interface%2Fsrc%2FOrm&show_perms_for=0&fu_action=">Файлы ORM</a></p>
<p><a href="/bitrix/admin/fileman_admin.php?PAGEN_1=1&SIZEN_1=20&lang=ru&site=s1&path=%2Flocal%2Fphp_interface%2Fconsole&show_perms_for=0&fu_action=">Файлы создания и удаления таблиц</a></p>
<p><a href="/bitrix/admin/fileman_admin.php?lang=ru&path=%2Fotus%2Fhomework4&site=s1">Файл ДЗ</a></p>



<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?> 
