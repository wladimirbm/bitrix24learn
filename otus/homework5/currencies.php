<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");?>
<?php
$APPLICATION->SetTitle("Компонент вывода валюты");
$APPLICATION->SetAdditionalCSS('/otus/homework3/style.css');
?>
<H1><? $APPLICATION->ShowTitle() ?></H1>

 <?$APPLICATION->IncludeComponent(
	"otus:showcurrency", 
	".default", 
	[
		"COMPONENT_TEMPLATE" => ".default",
		"CURRENCY" => "RUB",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600"
	],
	false
);?>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");?>