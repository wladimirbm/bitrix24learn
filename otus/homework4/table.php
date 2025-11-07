<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");?>
<?php
$APPLICATION->SetTitle("Собственная таблица");
$APPLICATION->SetAdditionalCSS('/otus/homework3/style.css');
?>
<H1><? $APPLICATION->ShowTitle() ?></H1>

<?php
//use Otus\Orm\BookTable;
use Otus\Orm\AssistentsTable;
use Otus\Orm\ProceduresTable;
use Otus\Orm\DutyTable;
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
    'PROCEDURE_ID' => 'PROCEDURES',
    '//PROCEDURE_NAME', // => 'PROCEDURES..NAME',
    'DUTY_ID' => 'DUTY',
    //'DUTY_NAME_V' => 'DUTY.PROPERTY_VALUES.NAME',
])
;

$assistResult = $query->exec();
$assists = [];
while ($assist = $assistResult->fetch()) {
    $customEntry = ProceduresTable::getById($assist['PROCEDURE_ID'])->fetchObject();
    
    if ($customEntry) {
    $iblockElements = $customEntry->getIblockElements();
    
    // Преобразование в массив с нужными полями
    $simpleArray = [];
    foreach ($iblockElements as $element) {
        $simpleArray[] = [
            'id' => $element->getId(),
            'name' => $element->getName(),
            'code' => $element->getCode(),
            'date' => $element->getDateActiveFrom()
        ];
    }
}
    dump($simpleArray);
    dump($assist); continue;
    // $procs = $assist->getProcedures();
    //  dump($procs);
    // // Способ 1: Перебор коллекции
    // foreach ($procs as $element) {
    //     echo "ID: " . $element->getId();
    //     echo "NAME: " . $element->get('NAME'); // Название записи инфоблока
    //     echo "NAME: " . $element->getName(); // Название записи инфоблока
    // }
      continue;
    
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
   
}

//dump($assists);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
