<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php 
    $APPLICATION->SetTitle("Домашняя работа 1");
?>
<H1><?$APPLICATION->ShowTitle()?></H1>
<?php
    dump($_SERVER);
    echo "<hr><pre>";
    print_r($_SERVER);
    echo "</pre>";
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
