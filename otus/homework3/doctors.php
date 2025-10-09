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

foreach ($doctors as $doctor) {
    dump($doctor->getId().' '.$doctor->getName().' - - -');
    dump(CFile::GetPath($doctor->getDetailPicture()));

    dump($doctor->getDuty()->getName()); 

    foreach($doctor->getProceduses()->getAll() as $prItem) {
        // получаем значение свойства Описание у процедуры 
        //if($prItem->getElement()->getDescription()!== null){
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
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
