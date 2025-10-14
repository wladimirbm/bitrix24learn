<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\UI\Extension::load('iblock.field-selector');
\Bitrix\Main\UI\Extension::load("ui.forms");
dump($_POST);
dump($_FILES);
if (!empty($_POST) && !empty($_POST['doctordata'])) {

    if (empty($_POST['docId'])) {  //new

        // добавление данных  записей в инфоблок

        if (!empty($_FILES['picture'])) {
            $picId = CFile::SaveFile($_FILES['picture'], "iblock");
        } else $picId = 0;

        $dbResult = \Bitrix\Iblock\Elements\ElementDoctorsTable::add([
            'NAME' => ($_POST['firstname'] ?? ' ') . ($_POST['lastname'] ?? ' ') . ($_POST['middlename'] ?? ''),
            'FIRSTNAME' => $_POST['firstname'],
            'LASTNAME' => $_POST['lastname'],
            'MIDDLENAME' => $_POST['middlename'],
            'BIRTHDAY' => $_POST['birthday'],
            'DETAIL_PICTURE' => $picId,
            'DUTY' => $_POST['duty'],
            'PROCEDURES' => $_POST['procedures'],

        ]);
    } else { //edit

        $docData = array(
            'NAME' => ($_POST['firstname'] ?? ' ') . ($_POST['lastname'] ?? ' ') . ($_POST['middlename'] ?? ''),
            'FIRSTNAME' => $_POST['firstname'],
            'LASTNAME' => $_POST['lastname'],
            'MIDDLENAME' => $_POST['middlename'],
            'BIRTHDAY' => $_POST['birthday'],
            //'DETAIL_PICTURE' => $picId,
            'DUTY' => $_POST['duty'],
            'PROCEDURES' => $_POST['procedures'],
        );

        if (!empty($_FILES['picture'])) {
            $picId = CFile::SaveFile($_FILES['picture'], "iblock");
            $docData['DETAIL_PICTURE'] = $picId;
        }

        $res = \Bitrix\Iblock\Elements\ElementDoctorsTable::update($_POST['docId'], $docData);
    }
    die();
}





$docId = 0;
if (!empty($_GET['docId']) && $_GET['docId'] == (int)$_GET['docId']) {
    $docId = (int)$_GET['docId'];
    $event = "Редактировать";
} else {
    $event = "Добавить";
    $docId = 0;
}
?>

<?php
$APPLICATION->SetTitle($event . " доктора");
?>
<H1><? $APPLICATION->ShowTitle() ?> <?php echo ' <a href="doctors.php">Вернуться к списку</a>'; ?></H1>

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
        'DUTY.ELEMENT.ID',
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
//dump($doctorDatas);


$elements = \Bitrix\Iblock\Elements\ElementProceduresTable::getList([ // car - cимвольный код API инфоблока
    'select' => ['NAME'], // имя свойства 
])->fetchCollection();
$PROCEDURES_NAME = [];
foreach ($elements as $element) {
    $PROCEDURES_NAME[] = $element->getName()->getValue(); // получение значения свойства MODEL
}
dump($PROCEDURES_NAME);

$elements = \Bitrix\Iblock\Elements\ElementDutysTable::getList([ // car - cимвольный код API инфоблока
    'select' => ['NAME'], // имя свойства 
])->fetchCollection();
$DUTY_NAME = [];
foreach ($elements as $element) {
    $DUTY_NAME[] = $element->getName()->getValue(); // получение значения свойства MODEL
}
dump($DUTY_NAME);

$doctor = [];
if (empty($doctorDatas) && !empty($docId))
    echo '<h2>Доктор не найден.</h2>';
else { //if(false)
    foreach ($doctorDatas as $doctorData) {
        $doctor['id'] =  $doctorData->getId();
        $doctor['name'] =  $doctorData->getName();
        $doctor['lastname'] =  $doctorData->getLastname()->getValue();
        $doctor['firstname'] =  $doctorData->getFirstname()->getValue();
        $doctor['middlename'] =  $doctorData->getMiddlename()->getValue();
        $doctor['birthday'] =  $doctorData->getBirthday()->getValue();
        $doctor['duty'] = $doctorData->getDuty()->getElement()->getName();
        $doctor['duty_id'] = $doctorData->getDuty()->getElement()->getId();
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
    <form action="" method="POSt" enctype="multipart/form-data">
        <table>
            <tr>
                <th colspan="2">
                    <input type="hidden" name="docId" value="<?php echo $docId; ?>">
                    <?php echo $doctor['name'] ?? "Новый" ?>
                </th>
            </tr>
            <tr>
                <th>
                    Фотография
                </th>
                <td>
                    <img src="<?php echo $doctor['picture'] ?? ''; ?>"><br>
                    <input type="file" name="picture">
                    <input type="hidden" name="editpicture" value="<?php echo $doctor['picture'] ?? ''; ?>">
                </td>
            </tr>
            <tr>
                <th>
                    Фамилия
                </th>
                <td>
                    <div class="ui-ctl ui-ctl-textbox">
                        <input type="text" class="ui-ctl-element" name="lastname" value="<?php echo $doctor['lastname'] ?? ''; ?>"> <!-- 2. Основное поле -->
                    </div>
                    <!-- <input type="text" name="lastname" value="<?php echo $doctor['lastname'] ?? ''; ?>"> -->
                </td>
            </tr>
            <tr>
                <th>
                    Имя
                </th>
                <td>
                    <div class="ui-ctl ui-ctl-textbox">
                        <input type="text" class="ui-ctl-element" name="firstname" value="<?php echo $doctor['firstname'] ?? ''; ?>"> <!-- 2. Основное поле -->
                    </div>
                    <!-- <input type="text" name="firstname" value="<?php echo $doctor['firstname'] ?? ''; ?>"> -->
                </td>
            </tr>
            <tr>
                <th>
                    Отчество
                </th>
                <td>
                    <div class="ui-ctl ui-ctl-textbox">
                        <input type="text" class="ui-ctl-element" name="middlename" value="<?php echo $doctor['middlename'] ?? ''; ?>"> <!-- 2. Основное поле -->
                    </div>
                    <!-- <input type="text" name="middlename" value="<?php echo $doctor['middlename'] ?? ''; ?>"> -->
                </td>
            </tr>
            <tr>

                <th>
                    Дата рождения
                </th>
                <td>
                    <?php
                    \Bitrix\Main\UI\Extension::load("ui.inputmask");
                    ?>
                    <input type="text" name="birthday" onclick="BX.calendar({node: this, field: this, bTime: false});" class="date-input ui-ctl-element date-input" value="<?php echo date('d.m.Y', strtotime($doctor['birthday'])) ?? ''; ?>">
                    <?php CJSCore::Init(array('date')); // подключаем инициализацию
                    ?>
                    <!-- <input type="text"
                        name="birthday"
                        onclick="BX.calendar({node: this, field: this, bTime: false});" /> -->
                    <script>
                        //import {Mask} from 'ui.inputmask';
                        const mask = new Mask({
                            //BX.UI.Inputmask
                            //BX.UI.FieldSelector
                            container: document.querySelector('.date-input'),
                            mask: 'xx.xx.xxxx'
                        });

                        mask.init()
                    </script>
                </td>
            </tr>
            <tr>

                <th>
                    Должность
                </th>
                <td>
                    <!-- <input type="text" name="duty" value="<?php echo $doctor['duty'] ?? ''; ?>"> -->
                    <?php


                    //\Bitrix\Main\UI\Extension::load('ui.field-selector');

                    $containerId = 'field-duty'; // ID dom-контейнера для TagSelector'а

                    if (!empty($doctor['duty']))
                        $values = [$doctor['duty_id']]; // текущее значение
                    else
                        $values = [];

                    $entities = [[], ['options' => ['enableSearch' => true]]];

                    $config = \Bitrix\Main\Web\Json::encode([
                        'containerId' => $containerId,
                        'fieldName' => 'duty',
                        'multiple' => false,
                        'collectionType' => 'int',
                        'selectedItems' => $values,
                        'iblockId' => 17,
                        'userType' => \Bitrix\Iblock\PropertyTable::USER_TYPE_ELEMENT_AUTOCOMPLETE,
                        'entityId' => \Bitrix\Iblock\Integration\UI\EntitySelector\IblockPropertyElementProvider::ENTITY_ID,
                        'entities' => $entities,

                    ]);

                    echo '
                            <div id="' . $containerId . '"></div>
                            <script>
                                (function() {
                                    const selector = new BX.Iblock.FieldSelector(' . $config . ');
                                    //const selector = new BX.UI.FieldSelector(' . $config . ');
                                    selector.render();
                                })();
                            </script>
                    ';

                    ?>
                </td>
            </tr>
            <tr>

                <th>
                    Процедуры
                </th>
                <td>
                    <?php


                    //\Bitrix\Main\UI\Extension::load('ui.field-selector');

                    $containerId = 'field-procedures'; // ID dom-контейнера для TagSelector'а

                    if (!empty($doctor['procs']))
                        $values = array_keys($doctor['procs']); // текущее значение
                    else
                        $values = [];

                    $entities = [[], ['options' => ['enableSearch' => true]]];

                    $config = \Bitrix\Main\Web\Json::encode([
                        'containerId' => $containerId,
                        'fieldName' => 'procedures[]',
                        'multiple' => true,
                        'collectionType' => 'int',
                        'selectedItems' => $values,
                        'iblockId' => 18,
                        'userType' => \Bitrix\Iblock\PropertyTable::USER_TYPE_ELEMENT_AUTOCOMPLETE,
                        'entityId' => \Bitrix\Iblock\Integration\UI\EntitySelector\IblockPropertyElementProvider::ENTITY_ID,
                        'entities' => $entities,

                    ]);

                    echo '
                            <div id="' . $containerId . '"></div>
                            <script>
                                (function() {
                                    const selector = new BX.Iblock.FieldSelector(' . $config . ');
                                    //const selector = new BX.UI.FieldSelector(' . $config . ');
                                    selector.render();
                                })();
                            </script>
                    ';

                    ?>
                </td>
            </tr>
            <tr>

                <th colspan=2>
                    <input type="submit" name="doctordata" value="<?php echo $event; ?>">
                </th>
            </tr>
        </table>
    </form>
<?php } ?>


<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>