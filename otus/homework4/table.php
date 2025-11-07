<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");?>
<?php
$APPLICATION->SetTitle("Собственная таблица");
$APPLICATION->SetAdditionalCSS('/otus/homework3/style.css');
?>
<H1><? $APPLICATION->ShowTitle() ?></H1>

<?php
//use Otus\Orm\BookTable;
use Otus\Orm\AssistentsTable;
use Bitrix\Main\Entity\Query;

/**
 * @var CMain $APPLICATION
 */


$query = new Query(AssistentsTable::class);
$query->setSelect([
    'ID',
    'FIRSTNAME',
    'ABOUT',
    'DOCTOR_FIRSTNAME' => 'DOCTORS.FIRSTNAME', //попробовать concat()
    'PROCEDURE_NAME' => 'PROCEDURES.PROPERTY_VALUES.NAME',
    //'PROCEDURE_NAME_V' => 'PROCEDURES.PROPERTY_VALUES.NAME',
    'DUTY_NAME' => 'DUTY.PROPERTY_VALUES.NAME',
    //'DUTY_NAME_V' => 'DUTY.PROPERTY_VALUES.NAME',
]);

$assistResult = $query->exec();
$assists = [];
while ($assist = $assistResult->fetch()) {
    $assistId = $assist['ID'];

    if (!isset($assists[$assistId])) {
        $assists[$assistId] = [
            'ID' => $assistId,
            'FIRSTNAME' => $assist['FIRSTNAME'],
            'ABOUT' => $assist['ABOUT'],
            'DUTY' => $assist['DUTY_NAME'],
            'DOCTORS' => [],
            'PROCEDURES' => [],
        ];
    }

    $assists[$assistId]['DOCTORS'][] = $assist['DOCTOR_FIRSTNAME'];
    $assists[$assistId]['PROCEDURES'][] = $assist['PROCEDURE_NAME'];

    /*
    $authorFullName = trim(sprintf('%s %s %s',
        $assist['AUTHOR_FIRST_NAME'] ?? '',
        $assist['AUTHOR_SECOND_NAME'] ?? '',
        $assist['AUTHOR_LAST_NAME'] ?? ''
    ));

    if (!empty($authorFullName) && !in_array($authorFullName, $assists[$assistId]['AUTHORS'])) {
        $assists[$assistId]['AUTHORS'][] = $authorFullName;
    }

    $editorFullName = trim(sprintf('%s %s %s',
        $assist['EDITOR_FIRST_NAME'] ?? '',
        $assist['EDITOR_SECOND_NAME'] ?? '',
        $assist['EDITOR_LAST_NAME'] ?? ''
    ));

    if (!empty($editorFullName) && !in_array($editorFullName, $assists[$assistId]['EDITORS'])) {
        $assists[$assistId]['EDITORS'][] = $editorFullName;
    }
    */
    dump($assist);
}

//dump($assists);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
