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


//$query = new Query(AssistentsTable::class);
$query = \Otus\Orm\AssistentsTable::query()
    ->setSelect([
        'ID',
        'FIRSTNAME', 
        'ABOUT',
        'PROCEDURE_ID' => 'RELATION.PROCEDURE_ID',
        'PROCEDURE_NAME' => 'PROCEDURE.NAME' // Стандартное поле NAME
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
        'PROCEDURE',
        (new \Bitrix\Main\ORM\Fields\Relations\Reference(
            'PROCEDURE',
            \Otus\Orm\ProceduresTable::class,
            ['=this.RELATION.PROCEDURE_ID' => 'ref.ID']
        ))->configureJoinType('INNER')
    )
    // УБИРАЕМ ФИЛЬТР - получаем все записи
    ->exec();

$assistents = [];

while ($item = $query->fetch()) {
    $assistentId = $item['ID'];
    
    // Если ассистент еще не добавлен в массив
    if (!isset($assistents[$assistentId])) {
        $assistents[$assistentId] = [
            'id' => $item['ID'],
            'firstname' => $item['FIRSTNAME'],
            'about' => $item['ABOUT'],
            'procedures' => []
        ];
    }
    
    // Добавляем процедуру к ассистенту
    if ($item['PROCEDURE_ID']) {
        $assistents[$assistentId]['procedures'][] = [
            'id' => $item['PROCEDURE_ID'],
            'name' => $item['PROCEDURE_NAME']
        ];
    }
}

// Выводим результат
foreach ($assistents as $assistent) {
    echo "Ассистент: {$assistent['firstname']} (ID: {$assistent['id']})";
    
    if (!empty($assistent['procedures'])) {
        foreach ($assistent['procedures'] as $procedure) {
            echo "  - Процедура: {$procedure['name']} (ID: {$procedure['id']})";
        }
    } else {
        echo "  - Нет связанных процедур";
    }
    echo "---";
}
//dump($assists);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
