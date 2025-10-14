<?php
if (!empty($_GET['delDoc'])) {
    $res = \Bitrix\Iblock\Elements\ElementDoctorsTable::delete((int)$_GET['delDoc']);
    LocalRedirect('/otus/homework3/doctors.php');
}

// use Bitrix\Main\UI\Extension;
// Extension::load('ui.bootstrap4');


require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Список докторов");
$APPLICATION->SetAdditionalCSS('/otus/homework3/style.css');
?>
<H1><? $APPLICATION->ShowTitle() ?></H1>
<?php

$doctors = \Bitrix\Iblock\Elements\ElementDoctorsTable::getList([ // получение списка процедур у врачей
    'select' => [
        'ID',
        'NAME',
        'DETAIL_PICTURE',
        'PROCEDURES.ELEMENT.NAME',
        'DUTY.ELEMENT.NAME',
    ],
    'filter' => [
        //'ID' => $docId,
        'ACTIVE' => 'Y',
    ],
])
    ->fetchCollection();

$doctorsList = [];
foreach ($doctors as $doctor) {
    $doctorsList[$doctor->getId()]['name'] = $doctor->getName() ?? '';
    echo $doctor->getId()."<br>";
    $doctorsList[$doctor->getId()]['duty'] = $doctor->getDuty()?? ''; //->getElement()->getName() ?? '';
    // dump($doctor->getId() . ' ' . $doctor->getName() . ' - - -');
    // dump(CFile::GetPath($doctor->getDetailPicture()));
    // dump($doctor->getDuty()->getElement()->getName());

    foreach ($doctor->getProcedures()->getAll() as $prItem) {
        // получаем значение свойства Описание у процедуры 
        //if($prItem->getElement()->getDescription()!== null){
        $doctorsList[$doctor->getId()]['proc'][$prItem->getId()] = $prItem->getElement()->getName() ?? '';
        // dump($prItem->getId() . ' - ' . $prItem->getElement()->getName()/*.' - '.$prItem->getElement()->getDescription()->getValue() */);
        //}
        // получаем значение свойства Цвет у процедуры 
        // foreach($prItem->getElement()->getColors()->getAll() as $color) {
        //     pr($color->getValue());
        // }
    }
}
echo "<hr>";

?>

<table>
    <tr>
        <th class="col-md-3">
            ID
        </th>
        <th class="col-md-3">
            ФИО
        </th>
        <th class="col-md-3">
            Должность
        </th>
        <th class="col-md-3">
            Процедуры
        </th>
        <th class="col-md-3">
            <a href="doctor.php">Добавить</a>
        </th>
    </tr>
    <?php foreach ($doctorsList as $id => $doc) { ?>
        <tr class="row">
            <td class="col-md-3">
                <?php echo $id; ?>
            </td>
            <td class="col-md-3">
                <?php echo $doc['name']; ?>
            </td>
            <td class="col-md-3">
                <?php echo $doc['duty']; ?>
            </td>
            <td class="col-md-3">
                <?php foreach ($doc['proc'] as $proc) {
                    echo $proc . "<br>";
                } ?>
            </td>
            <td class="col-md-3">
                <a href="doctor.php?docId=<?php echo $id; ?>">Редактировать</a><br />
                <a href="doctors.php?delDoc=<?php echo $id; ?>">Удалить</a>
            </td>
        </tr>
    <?php } ?>
</table>


<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>