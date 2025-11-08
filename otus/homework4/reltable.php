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
use Otus\Orm\ProceduresAssistentTable;
use Otus\Orm\DutyTable;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\ExpressionField;

/**
 * @var CMain $APPLICATION
 */

$query = AssistentsTable::query()
    ->setSelect([
        'ID',
        'LASTNAME', 
        'FIRSTNAME', 
        'ABOUT',
        'DUTY_ID',
        'DUTY',
        'DOCTOR_ID' => 'DOCTORS.ID',
        'DOCTOR_FIRSTNAME' => 'DOCTORS.FIRSTNAME',
        'DOCTOR_LASTNAME' => 'DOCTORS.LASTNAME',
        'PROCEDURE_ELEMENTS' => 'PROCEDURES.ELEMENTS',
        'PROCEDURE_ID' => 'RELATION.PROCEDURE_ID',
        'PROCEDURE_NAME', //через ExpressionField
        'DUTY_NAME', // через ExpressionField
    ])
    ->registerRuntimeField(
        'RELATION',
        (new Reference(
            'RELATION',
            ProceduresAssistentTable::class,
            ['=this.ID' => 'ref.ASSISTENT_ID']
        ))->configureJoinType('INNER')
    )
    ->registerRuntimeField(
        (new ExpressionField(
            'PROCEDURE_NAME',
            '(SELECT NAME FROM b_iblock_element WHERE ID = %s)',
            ['RELATION.PROCEDURE_ID']
        ))
    )
    ->registerRuntimeField(
        (new ExpressionField(
            'DUTY_NAME',
            '(SELECT NAME FROM b_iblock_element WHERE ID = %s)',
            ['DUTY_ID']
        ))
    )
    ->exec();

$assistents = [];

while ($item = $query->fetch()) {
    dump($item); continue;
    $assistentId = $item['ID'];
    
    if (!isset($assistents[$assistentId])) {
        $assistents[$assistentId] = [
            'id' => $item['ID'],
            'firstname' => $item['FIRSTNAME'],
            'lastname' => $item['LASTNAME'],
            'about' => $item['ABOUT'],
            'duty_id' => $item['DUTY_ID'],
            'duty' => $item['DUTY_NAME'],
            'doctors' => [],
            'procedures' => [],
        ];
    }
    
    if ($item['PROCEDURE_ID'] && $item['PROCEDURE_NAME'] && empty($assistents[$assistentId]['procedures'][$item['PROCEDURE_ID']])) {
        $assistents[$assistentId]['procedures'][$item['PROCEDURE_ID']] = [
            'id' => $item['PROCEDURE_ID'],
            'name' => $item['PROCEDURE_NAME']
        ];
    }    
    if ($item['DOCTOR_ID'] && $item['DOCTOR_FIRSTNAME'] && empty($assistents[$assistentId]['doctors'][$item['DOCTOR_ID']])) {
        $assistents[$assistentId]['doctors'][$item['DOCTOR_ID']] = [
            'id' => $item['DOCTOR_ID'],
            'firstname' => $item['DOCTOR_FIRSTNAME'],
            'lastname' => $item['DOCTOR_LASTNAME']
        ];
    }
}
//dump($assistents);

?>
<table><thead>
    <tr>
    <th>Ассистент</th>
    <th>Должность</th>
    <th>Процедуры</th>
    <th>Доктора</th>
    </tr>
</thead>
<tbody>
<?php
foreach ($assistents as $assistent) {
    echo '<tr class="row">';
    echo "<td>(ID: {$assistent['id']}): {$assistent['lastname']} {$assistent['firstname']}</td>";
    echo "<td>(ID: {$assistent['duty_id']}): {$assistent['duty']}</td>";
   
    echo "<td>";
    if (!empty($assistent['procedures'])) {
        foreach ($assistent['procedures'] as $procedure) {
            echo "(ID: {$procedure['id']}): {$procedure['name']} <br>";
        }
    } else {
        echo "Нет связанных процедур";
    }  
    echo "</td>";
    echo "<td>";
    if (!empty($assistent['doctors'])) {
        foreach ($assistent['doctors'] as $doctors) {
            echo "(ID: {$doctors['id']}): {$doctors['lastname']} {$doctors['firstname']} <br>";
        }
    } else {
        echo "Нет связанных докторов";
    }
    echo "</td>";
    echo "</tr>";
}
//dump($assists);
echo "</tbody>";
echo "</table>";

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
