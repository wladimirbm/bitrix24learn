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


// ДИАГНОСТИКА - какие поля доступны в ProceduresTable
$entity = \Otus\Orm\ProceduresTable::getEntity();
$fields = $entity->getFields();

echo "Доступные поля в ProceduresTable:\n";
foreach ($fields as $fieldName => $field) {
    echo "- $fieldName (тип: " . get_class($field) . ")\n";
}

$query = \Otus\Orm\AssistentsTable::query()
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
        'PROCEDURE_ID' => 'RELATION.PROCEDURE_ID',
        'PROCEDURE_NAME', // Будет создано через ExpressionField
        'DUTY_NAME', // Будет создано через ExpressionField
    ])
    ->registerRuntimeField(
        'RELATION',
        (new \Bitrix\Main\ORM\Fields\Relations\Reference(
            'RELATION',
            \Otus\Orm\ProceduresAssistentTable::class,
            ['=this.ID' => 'ref.ASSISTENT_ID']
        ))->configureJoinType('INNER')
    )
    ->registerRuntimeField(
        (new \Bitrix\Main\ORM\Fields\ExpressionField(
            'PROCEDURE_NAME',
            '(SELECT NAME FROM b_iblock_element WHERE ID = %s)',
            ['RELATION.PROCEDURE_ID']
        ))
    )
    ->registerRuntimeField(
        (new \Bitrix\Main\ORM\Fields\ExpressionField(
            'DUTY_NAME',
            '(SELECT NAME FROM b_iblock_element WHERE ID = %s)',
            ['DUTY_ID']
        ))
    )
    ->exec();

$assistents = [];

while ($item = $query->fetch()) {
    $assistentId = $item['ID'];
    
    if (!isset($assistents[$assistentId])) {
        $assistents[$assistentId] = [
            'id' => $item['ID'],
            'firstname' => $item['FIRSTNAME'],
            'lastname' => $item['LASTNAME'],
            'about' => $item['ABOUT'],
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
dump($assistents);

// Выводим результат
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
    echo "<td>(ID: {$assistent['DUTY_ID']}): {$assistent['DUTY_NAME']}</td>";
   
    echo "<td>";
    if (!empty($assistent['procedures'])) {
        foreach ($assistent['procedures'] as $procedure) {
            echo "(ID: {$procedure['id']}): {$procedure['name']} <br>";
        }
    } else {
        echo "  - Нет связанных процедур";
    }  
    echo "</td>";
    echo "<td>";
    if (!empty($assistent['doctors'])) {
        foreach ($assistent['doctors'] as $doctors) {
            echo "(ID: {$doctors['id']}): {$doctors['name']} <br>";
        }
    } else {
        echo "  - Нет связанных докторов";
    }
    echo "</td>";
    echo "</tr>";
}//dump($assists);
echo "</tbody>";
echo "</table>";

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
