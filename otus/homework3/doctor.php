<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
if(!empty($_GET['docId']))
        $dicId = (int)$_GET['docId'];
$doctorData = \Bitrix\Iblock\Elements\ElementDoctorsTable::getList([ // получение списка процедур у врачей
    'select' => [
        'ID',
        'NAME',
        'DETAIL_PICTURE',
        'PROCEDURES.ELEMENT.NAME',
        'DUTY.ELEMENT.NAME',
        //'PROCEDURES.ELEMENT.DESCRIPTION', // PROC_IDS_MULTI - множественное поле Процедуры у элемента инфоблока Доктора 
        //'PROCEDURES.ELEMENT.COLORS'
    ],
    'filter' => [
        'ID' => $docId,
        'ACTIVE' => 'Y',
    ],
])
    ->fetchObject();
dump($doctorData);

    if(empty($doctorData)) 
        $event = "Добавить";
    else $event = "Редактировать";
?>
<?php
$APPLICATION->SetTitle($event." доктора");
?>
<H1><? $APPLICATION->ShowTitle() ?></H1>

<?
$doctorsList = [];
foreach ($doctors as $doctor) {
    $doctorsList['name'] = $doctor->getName();
    $doctorsList['duty'] = $doctor->getDuty()->getElement()->getName();
    // dump($doctor->getId() . ' ' . $doctor->getName() . ' - - -');
    // dump(CFile::GetPath($doctor->getDetailPicture()));
    // dump($doctor->getDuty()->getElement()->getName());

    foreach ($doctor->getProcedures()->getAll() as $prItem) {
        // получаем значение свойства Описание у процедуры 
        //if($prItem->getElement()->getDescription()!== null){
        $doctorsList[$doctor->getId()]['proc'][$prItem->getId()] = $prItem->getElement()->getName();
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
<style>
    table th,table td {
        padding: 10px;
        border: 1px solid grey;
        border-radius: 5px;
    }
</style>
<table>
    <tr>
        <th colspan="2">

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
            <a href="#">Добавить</a>
        </th>
    </tr>
</table>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>