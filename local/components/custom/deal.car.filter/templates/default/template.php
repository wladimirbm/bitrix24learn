<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

// Передаем данные в JavaScript
?>
<script>
// Глобальные переменные для фильтра автомобилей
window.DEAL_CAR_FILTER_CONFIG = {
    contactId: <?= CUtil::PhpToJSObject($arResult['CONTACT_ID']) ?>,
    garageEntityId: <?= CUtil::PhpToJSObject($arResult['GARAGE_ENTITY_ID']) ?>,
    carFieldCode: <?= CUtil::PhpToJSObject($arResult['CAR_FIELD_CODE']) ?>,
    isEditPage: <?= (preg_match('#/crm/deal/edit/#', $_SERVER['REQUEST_URI']) ? 'true' : 'false') ?>
};
</script>

<?php
// Подключаем основной JS файл
$asset = \Bitrix\Main\Page\Asset::getInstance();
$asset->addJs('/local/js/deal_car_filter.js');
?>