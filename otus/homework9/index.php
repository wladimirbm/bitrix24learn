<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Домашняя работа 9 (Написание своих активити для БП // ДЗ)");
?>
<H1><? $APPLICATION->ShowTitle() ?></H1>

<h2>Написание своих активити для БП</h2>

<p><a href="/services/lists/19/view/0/">Инфоблок</a></p>

<h2>Файлы</h2>
<p><a href="/bitrix/admin/fileman_admin.php?PAGEN_1=1&SIZEN_1=20&lang=ru&site=s1&path=%2Flocal%2Factivities%2Fcustom%2Fcreatecompanybyinnactivity&show_perms_for=0&fu_action=">Файлы активити</a></p>
<p><a href="/services/lists/19/bp_edit/6/">Бизнес процесс</a></p>
 

<?php

$token = "261c33510bdc7d1bb98565cfd1e37425b2ee3b2a";
$secret = "832a12ffd03501b87df857c63b3eda10c205e44a";

$dadata = new Dadata($token, $secret);
$dadata->init();

// Найти компанию по ИНН
$fields = array("query" => "7707083893", "count" => 1);
$result = $dadata->suggest("party", $fields);
dump($result);

?>



<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>