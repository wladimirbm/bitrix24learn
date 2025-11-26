<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Домашняя работа 9 (Написание своих активити для БП // ДЗ)");
?>
<H1><? $APPLICATION->ShowTitle() ?></H1>

<h2>Написание своих активити для БП</h2>

<p><a href="/crm/">CRM</a></p>

<h2>Файлы</h2>
<p><a href="/bitrix/admin/perfmon_tables.php?lang=ru#mod_">Таблицы</a></p>

<?php
$token = "261c33510bdc7d1bb98565cfd1e37425b2ee3b2a";
$secret = "832a12ffd03501b87df857c63b3eda10c205e44a";

$dadata = new Dadata($token, $secret);
$dadata->init();

// Найти компанию по ИНН
$fields = array("query" => "7707083893", "count" => 5);
$result = $dadata->suggest("party", $fields);
dump($result);
?>



<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>