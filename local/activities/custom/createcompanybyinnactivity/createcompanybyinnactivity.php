<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\Activity\PropertiesDialog;

class CBPCreateCompanyByInnactivity extends BaseActivity
{
    // protected static $requiredModules = ["crm"];

    /**
     * @see parent::_construct()
     * @param $name string Activity name
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $this->arProperties = [
            'InnField' => '',
            //'MaxCountCreate' => '1',

            // return
            'Text' => null,
        ];

        $this->SetPropertiesTypes([
            'Text' => ['Type' => FieldType::STRING],
        ]);
    }

    /**
     * Return activity file path
     * @return string
     */
    protected static function getFileName(): string
    {
        return __FILE__;
    }

    /**
     * @return ErrorCollection
     */
    protected function internalExecute(): ErrorCollection
    {
        $errors = parent::internalExecute();

        $token = "261c33510bdc7d1bb98565cfd1e37425b2ee3b2a";
        $secret = "832a12ffd03501b87df857c63b3eda10c205e44a";

        // token и secret лучше передавать в виде переменных БП в активити
        // $rootActivity = $this->GetRootActivity();
        // $token = $rootActivity->GetVariable("TOKEN"); 
        // $secret =  $rootActivity->GetVariable("SECRET"); 

        \Bitrix\Main\Loader::includeModule('crm');

        $response['suggestions'][0]['value'] = "Первая";
        $response['suggestions'][0]['address']['value'] = "Москва";


        /*
        $dadata = new Dadata($token, $secret);
        $dadata->init();

        $fields = array("query" => $this->InnField, "count" => 5);
        $response = $dadata->suggest("party", $fields);
*/
        $companyName = 'Компания не найдена!';


        if (!empty($response['suggestions'])) { // если копания найдена
            // по ИНН возвращается массив в котором может бытьнесколько элементов (компаний)
            $companyName = $response['suggestions'][0]['value']; // получаем имя компании из первого элемента  
            $arNewCompany = array(
                "TITLE" => $response['suggestions'][0]['value'],
                "OPENED" => "Y",
                "COMPANY_TYPE" => "CUSTOMER",
                "ASSIGNED_BY_ID" => 1,
                "ADDRESS" => $response['suggestions'][0]['address']['value'],
            );
            $company = new CCrmCompany(false);

            $arFilter = array("TITLE" => $response['suggestions'][0]['value']);
            $arSelect = array("ID", "TITLE");

            $rsCompany = CCrmCompany::GetList(array(), $arFilter, $arSelect);
            if ($arComp = $rsCompany->Fetch()) {
                $companyID = $arComp['ID'];
                $this->preparedProperties['Text'] = 'Найдена компания ID: ' . $companyID;
                $this->log($this->preparedProperties['Text']);
            } else {
                $companyID = $company->Add($arNewCompany);
                $this->preparedProperties['Text'] = 'Добавлена компания: ' . $companyName . '- ' . $this->InnField;
                $this->log($this->preparedProperties['Text']);
            }

            $rootActivity = $this->GetRootActivity(); // получаем объект активити

            $documentService = CBPRuntime::GetRuntime(true)->getDocumentService();
            $documentType = $rootActivity->getDocumentType(); // получаем тип документа
            $documentId = $rootActivity->getDocumentId(); // получаем ID документа 

            $fields = [
                'NAME' => $response['suggestions'][0]['value'],
            ];

            $iblockElement = new CIBlockElement();

            $result = $iblockElement->Update($documentId, $fields);

            $properties = [
                'CUSTOMER' => $companyID,
            ];

            $result = CIBlockElement::SetPropertyValuesEx(
                $documentId,
                false,
                $properties
            );
        }

        // в рабочем активити необходимо будет создать отдельный метод который будет получать результат ответа сервиса Dadata, 
        // обходить циклом результат и сохранять в массив все полученные организации


        /*
        $rootActivity = $this->GetRootActivity(); // получаем объект активити
        // сохранение полученных результатов работы активити в переменную бизнес процесса
        // $rootActivity->SetVariable("TEST", $this->preparedProperties['Text']); 

        // получение значения полей документа в активити        
        $documentType = $rootActivity->getDocumentType(); // получаем тип документа
        $documentId = $rootActivity->getDocumentId(); // получаем ID документа 

        // получаем объект документа над которым выполняется БП (элемент сущности Компания)
        $documentService = CBPRuntime::GetRuntime(true)->getDocumentService(); 
        // $documentService = $this->workflow->GetService("DocumentService");   

        // поля документа
        $documentFields =  $documentService->GetDocumentFields($documentType);
        //$arDocumentFields = $documentService->GetDocument($documentId);   

        foreach ($documentFields as $key => $value) {
            if($key == 'UF_CRM_1718872462762'){ // поле номер ИНН
                $fieldValue = $documentService->getFieldValue($documentId, $key, $documentType);
                $this->log('значение поля Инн:'.' '.$fieldValue);
            }

            if($key == 'UF_COMPANY_INN'){ // поле UF_COMPANY_INN
                $fieldValue = $documentService->getFieldValue($documentId, $key, $documentType);
                $this->log('значение поля UF_COMPANY_INN:'.' '.$fieldValue);
            }
        }*/


        return $errors;
    }

    /**
     * @param PropertiesDialog|null $dialog
     * @return array[]
     */
    public static function getPropertiesDialogMap(?PropertiesDialog $dialog = null): array
    {
        $map = [
            'InnField' => [
                'Name' => Loc::getMessage('CCBYINN_ACTIVITY_FIELD_SUBJECT'),
                'FieldName' => 'InnField',
                'Type' => FieldType::STRING,
                'Required' => true,
                'Options' => [],
            ],
        ];
        return $map;
    }
}
