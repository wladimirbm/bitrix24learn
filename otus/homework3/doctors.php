<?php
// use Bitrix\Main\UI\Extension;
// Extension::load('ui.bootstrap4');
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php 
    $APPLICATION->SetTitle("Список докторов");
?>
<H1><?$APPLICATION->ShowTitle()?></H1>
<?php
$doctors = \Bitrix\Iblock\Elements\ElementDoctorsTable::getList([ // получение списка процедур у врачей
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
        //'ID' => $docId,
        'ACTIVE' => 'Y',
    ],
])
->fetchCollection(); 

$doctorsList = [];
foreach ($doctors as $doctor) {
    $doctorsList[$doctor->getId()]['name']=$doctor->getName();
    $doctorsList[$doctor->getId()]['duty']=$doctor->getDuty()->getElement()->getName();
    
    dump($doctor->getId().' '.$doctor->getName().' - - -');
    dump(CFile::GetPath($doctor->getDetailPicture()));

    dump($doctor->getDuty()->getElement()->getName()); 

    foreach($doctor->getProcedures()->getAll() as $prItem) {
        // получаем значение свойства Описание у процедуры 
        //if($prItem->getElement()->getDescription()!== null){
        $doctorsList[$doctor->getId()]['proc'][$prItem->getId()]=$prItem->getElement()->getName();
        dump($prItem->getId().' - '.$prItem->getElement()->getName()/*.' - '.$prItem->getElement()->getDescription()->getValue() */);
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
    .table{ 
    }
    .table .row {
        display: flex;
        align-items:baseline;
        justify-content: space-between;
        padding: 10px;
        border: 1px solid grey;
        border-radius:5px;
    }
</style>
<div class="table">
    <div class="row">
        <div class="col-md-3">
        ФИО
        </div>
        <div class="col-md-3">
        Должность
        </div>
        <div class="col-md-3">
        Процедуры
        </div>
        <div class="col-md-3">
            
        </div>
    </div>
<?php foreach($doctorsList as $doc) { ?>
    <div class="row">
        <div class="col-md-3">
         <?php echo $doc['name']; ?>   
        </div>
        <div class="col-md-3">
         <?php echo $doc['duty']; ?>   
        </div>
        <div class="col-md-3">
         <?php foreach($doc['proc'] as $proc) { 
            echo $proc."<br>";
          } ?>
        </div>
        <div class="col-md-3">
            <a href="#">Редактировать</a>
        </div>
    </div>
<?php } ?>
</div>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
