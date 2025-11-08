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
    'PROCEDURES',// => 'PROCEDURES',
    //'PROCEDURE_NAME', // => 'PROCEDURES..NAME',
    'DUTY' => 'DUTY',
    // 'PROCEDURE_ID' => 'RELATION.PROCEDURE_ID',
    // 'PROCEDURE_NAME' => 'PROCEDURE.NAME'
    //'DUTY_NAME_V' => 'DUTY.PROPERTY_VALUES.NAME',
    // 'PROCEDURE_NAME', // Название процедуры из инфоблока
    // 'DUTY_NAME', // Название процедуры из инфоблока
  //    'PROCEDURE_ID' => 'RELATION.PROCEDURE_ID',
        'PROCEDURE_NAME' => 'PROCEDURES.NAME',
    ])
    // ->registerRuntimeField(
    //     'RELATION',
    //     (new \Bitrix\Main\ORM\Fields\Relations\Reference(
    //         'RELATION',
    //         \Otus\Orm\ProceduresAssistentTable::class,
    //         ['=this.ID' => 'ref.ASSISTENT_ID']
    //     ))->configureJoinType('INNER')
    // )
    // ->registerRuntimeField(
    //     'PROCEDURE',
    //     (new \Bitrix\Main\ORM\Fields\Relations\Reference(
    //         'PROCEDURE',
    //         \Otus\Orm\ProceduresTable::class,
    //         ['=this.RELATION.PROCEDURE_ID' => 'ref.ID']
    //     ))->configureJoinType('INNER')
    // )
    // ->registerRuntimeField(
    //     (new \Bitrix\Main\ORM\Fields\ExpressionField(
    //         'PROCEDURE_NAME',
    //         '%s',
    //         ['PROCEDURE.NAME']
    //     ))
    // )


//     ->registerRuntimeField(
//         (new \Bitrix\Main\ORM\Fields\ExpressionField(
//             'PROCEDURE_NAME',
//             '(SELECT NAME FROM b_iblock_element WHERE ID = (
//                 SELECT PROCEDURE_ID 
//                 FROM otus_procedures_assistent 
//                 WHERE ASSISTENT_ID = %s 
//                 LIMIT 1
//             ))',
//             ['ID']
//         ))
//     )
//  ->registerRuntimeField(
//         (new \Bitrix\Main\ORM\Fields\ExpressionField(
//             'DUTY_NAME',
//             '(SELECT NAME FROM b_iblock_element WHERE ID = %s 
//                 LIMIT 1
//             )',
//             ['DUTY_ID']
//         ))
//     )


    // ->registerRuntimeField(
    //     'RELATION',
    //     (new \Bitrix\Main\ORM\Fields\Relations\Reference(
    //         'RELATION',
    //         \Otus\Orm\ProceduresAssistentTable::class,
    //         ['=this.ID' => 'ref.ASSISTENT_ID']
    //     ))->configureJoinType('INNER')
    // )
    // ->registerRuntimeField(
    //     'PROCEDURE',
    //     (new \Bitrix\Main\ORM\Fields\Relations\Reference(
    //         'PROCEDURE',
    //         \Otus\Orm\ProceduresTable::class,
    //         ['=this.RELATION.PROCEDURE_ID' => 'ref.ID']
    //     ))->configureJoinType('INNER')
    // )
;
$assistResult = $query->exec();

$assists = [];
while ($assist = $assistResult->fetchObject()) {
  echo "Ассистент: {$assist['FIRSTNAME']}";
  
    $customEntry = ProceduresTable::getById(300)->fetchObject();
    dump($customEntry);

//    $procedures = $assistent->getProcedures();
//     foreach ($procedures as $procedure) {
//         echo "Процедура: " . $procedure->get('NAME');
//     }

    // echo "Процедура: {$assist['PROCEDURE_NAME']}"; // NAME записи инфоблока
    // echo "Должность: {$assist['DUTY_NAME']}"; // NAME записи инфоблока
    dump($assist); continue;
 
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
