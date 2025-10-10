<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>

<?php

$docId = 0;
if (!empty($_GET['docId']) && $_GET['docId'] == (int)$_GET['docId']) {
    $docId = (int)$_GET['docId'];
    $event = "Редактировать";
} else $event = "Добавить";
?>

<?php
$APPLICATION->SetTitle($event . " доктора");
?>
<H1><? $APPLICATION->ShowTitle() ?></H1>

<?php


$doctorDatas = \Bitrix\Iblock\Elements\ElementDoctorsTable::getList([ // получение списка процедур у врачей
    'select' => [
        'ID',
        'NAME',
        'FIRSTNAME',
        'LASTNAME',
        'MIDDLENAME',
        'BIRTHDAY',
        'DETAIL_PICTURE',
        'PROCEDURES.ELEMENT.ID',
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
    ->fetchCollection();
dump($doctorDatas);
$doctor = [];
if (empty($doctorDatas) && !empty($docId))
    echo '<h2>Доктор не найден. <a href="doctors.php">Вернуться к списку</a></h2>';
else { //if(false)
    foreach ($doctorDatas as $doctorData) {
        $doctor['id'] =  $doctorData->getId();
        $doctor['name'] =  $doctorData->getName();
        $doctor['lastname'] =  $doctorData->getLastname()->getValue();
        $doctor['firstname'] =  $doctorData->getFirstname()->getValue();
        $doctor['middlename'] =  $doctorData->getMiddlename()->getValue();
        $doctor['birthday'] =  $doctorData->getBirthday()->getValue();
        $doctor['duty'] = $doctorData->getDuty()->getElement()->getName();
        $doctor['picture'] = CFile::GetPath($doctorData->getDetailPicture());
        
        foreach ($doctorData->getProcedures()->getAll() as $prItem) {
            //dump($prItem);
            $doctor['procs'][$prItem->getElement()->getId()] = $prItem->getElement()->getName();
        }
    }
    dump($doctor);
?>


    <?
    /*
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
            // dump($prItem->getId() . ' - ' . $prItem->getElement()->getName());//.' - '.$prItem->getElement()->getDescription()->getValue() 
            //}
            // получаем значение свойства Цвет у процедуры 
            // foreach($prItem->getElement()->getColors()->getAll() as $color) {
            //     pr($color->getValue());
            // }
        }
    }
    echo "<hr>";
*/
    ?>

    <style>
        table th,
        table td {
            padding: 10px;
            border: 1px solid grey;
            border-radius: 5px;
        }
    </style>
    <table>
        <tr>
            <th colspan="2">
                <?php echo $doctor['name'] ?? "Новый" ?>
            </th>
        </tr>
        <tr>
            <th>
                Фотография
            </th>
            <td>
                <img src="<?php echo $doctor['picture'] ?? ''; ?>">
                <input type="file" name="picture">
                <input type="hidden" name="editpicture" value="<?php echo $doctor['picture'] ?? ''; ?>">
            </td>
        </tr>
        <tr>
            <th>
                Фамилия
            </th>
            <td>
                <input type="text" name="lastname" value="<?php echo $doctor['lastname'] ?? ''; ?>">
            </td>
        </tr>
        <tr>
            <th>
                Имя
            </th>
            <td>
                <input type="text" name="firstname" value="<?php echo $doctor['firstname'] ?? ''; ?>">
            </td>
        </tr>
        <tr>
            <th>
                Отчество
            </th>
            <td>
                <input type="text" name="middlename" value="<?php echo $doctor['middlename'] ?? ''; ?>">
            </td>
        </tr>
        <tr>

            <th>
                Дата рождения
            </th>
            <td>
                <input type="text" name="duty" value="<?php echo $doctor['birthday'] ?? ''; ?>">
            </td>
        </tr>
        <tr>

            <th>
                Должность
            </th>
            <td>
                <input type="text" name="duty" value="<?php echo $doctor['duty'] ?? ''; ?>">
            </td>
        </tr>
        <tr>

            <th>
                Процедуры
            </th>
            <td>
               <?php

                    \Bitrix\Main\Loader::includeModule('iblock');

                    \Bitrix\Main\UI\Extension::load('iblock.field-selector');
                    //\Bitrix\Main\UI\Extension::load('ui.field-selector');

                    $containerId = 'field-procedures'; // ID dom-контейнера для TagSelector'а

                    if(!empty($doctor['procs']))
                        $values = array_keys($doctor['procs']); // текущее значение
                    else
                        $values = [];

                    $entities = [[],['options'=> ['enableSearch' => true]]];

                    $config = \Bitrix\Main\Web\Json::encode([
                        'containerId' => $containerId,
                        'fieldName' => 'procedures',
                        'multiple' => true,
                        'collectionType' => 'int',
                        'selectedItems' => $values,
                        'iblockId' => 18,
                        'userType' => \Bitrix\Iblock\PropertyTable::USER_TYPE_ELEMENT_AUTOCOMPLETE,
                        'entityId' => \Bitrix\Iblock\Integration\UI\EntitySelector\IblockPropertyElementProvider::ENTITY_ID,
                        'entities' => $entities,
            
                    ]);

                    echo '
                            <div id="'.$containerId.'"></div>
                            <script>
                                (function() {
                                    const selector = new BX.Iblock.FieldSelector('.$config.');
                                    //const selector = new BX.UI.FieldSelector('.$config.');
                                    selector.render();
                                })();
                            </script>
                    ';

                                                            ?>
            </td>
        </tr>
        <tr>

            <th colspan=2>
                <input type="button" name="doctordata" value="<?php echo $event; ?>">
            </th>
        </tr>
    </table>
<?php } ?>


<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>