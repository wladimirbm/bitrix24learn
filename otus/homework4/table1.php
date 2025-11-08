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
        'FIRSTNAME', 
        'ABOUT',
        'DUTY_ID',
        'DUTY',
        'DOCTOR_ID' => 'DOCTORS.ID',
        'DOCTOR_FIRSTNAME' => 'DOCTORS.FIRSTNAME',
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
    dump($item);
    if (!isset($assistents[$assistentId])) {
        $assistents[$assistentId] = [
            'id' => $item['ID'],
            'firstname' => $item['FIRSTNAME'],
            'about' => $item['ABOUT'],
            'procedures' => []
        ];
    }
    
    if ($item['PROCEDURE_ID'] && $item['PROCEDURE_NAME']) {
        $assistents[$assistentId]['procedures'][] = [
            'id' => $item['PROCEDURE_ID'],
            'name' => $item['PROCEDURE_NAME']
        ];
    }
}

// Выводим результат
foreach ($assistents as $assistent) {
    echo "<br>Ассистент: {$assistent['firstname']} (ID: {$assistent['id']})";
    echo "Должность: {$assistent['DUTY_NAME']} (ID: {$assistent['DUTY_ID']})";
    echo "Доктор: {$assistent['DOCTOR_FIRSTNAME']} (ID: {$assistent['DOCTOR_ID']})";
    
    if (!empty($assistent['procedures'])) {
        foreach ($assistent['procedures'] as $procedure) {
            echo "  - Процедура: {$procedure['name']} (ID: {$procedure['id']})";
        }
    } else {
        echo "  - Нет связанных процедур";
    }
    echo "--- <hr>";
}//dump($assists);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
