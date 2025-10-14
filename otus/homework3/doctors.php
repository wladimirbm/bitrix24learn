<?php
// use Bitrix\Main\UI\Extension;
// Extension::load('ui.bootstrap4');
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
if (!empty($_GET['delDoc'])) {
    $res = \Bitrix\Iblock\Elements\ElementDoctorsTable::delete((int)$_GET['delDoc']);
    LocalRedirect('/otus/homework3/doctors.php');
} 
\Bitrix\Main\UI\Extension::load("ui.dialogs.messagebox");
?>
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
        'PROCEDURES_ID.ELEMENT.NAME',
        'DUTY_ID.ELEMENT.NAME',
        'DUTY_NAME' => 'DUTY_ID.ELEMENT.NAME'
    ],
    'filter' => [
        //'ID' => $docId,
        'ACTIVE' => 'Y',
    ],
])
//->fetch();    
//->fetchAll();    
->fetchCollection();

dump($doctors);

$doctorsList = [];
foreach ($doctors as $doctor) {
    $doctorsList[$doctor->getId()]['name'] = $doctor->getName() ?? '';
    //echo $doctor->getId()."<br>";
    $doctorsList[$doctor->getId()]['duty'] = $doctor->getDutyId()->getElement()->getName()??'';//->getElement()->getName() ?? ''; //->getElement()->getName() ?? '';
   
    // dump($doctor->getId() . ' ' . $doctor->getName() . ' - - -');
    // dump(CFile::GetPath($doctor->getDetailPicture()));
    // dump($doctor->getDuty()->getElement()->getName());

    foreach ($doctor->getProceduresId()->getAll() as $prItem) {
        // получаем значение свойства Описание у процедуры 
        //if($prItem->getElement()->getDescription()!== null){
        $doctorsList[$doctor->getId()]['proc'][$prItem->getId()] = $prItem->getElement()->getName() ?? '';
        // dump($prItem->getId() . ' - ' . $prItem->getElement()->getName()/*.' - '.$prItem->getElement()->getDescription()->getValue() */);

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
                <a href="doctors.php?delDoc=<?php echo $id; ?>" onClick='confDel();'>Удалить</a>
            </td>
        </tr>
    <?php } ?>
</table>

<script>

    function confDel(e) {
        e.preventDefault();
        BX.UI.Dialogs.MessageBox.confirm("Удалить", () => { return false }, "Да", () => { return true; });
    }
    
</script>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>