<?php
//13w08kkkeoqxqjn60xm41x0emdadivuu

// use App\Debug\Mylog;
// Mylog::addLog($_REQUEST, '$_REQUEST', '', __FILE__, __LINE__);
//echo "hello";
file_put_contents('../../../logs/webhook.log', print_r($_REQUEST, true) . PHP_EOL, FILE_APPEND);
//https://cc61466.tw1.ru/rest/1/kuzuzl8fna81k2n0/

require_once('crest.php');

if (!empty($_REQUEST['data']['FIELDS']['ID']))
    $resultActivity = CRest::call(
        'crm.activity.get',
        [
            'id' => $_REQUEST['data']['FIELDS']['ID']
        ]
    );

file_put_contents('../../../logs/webhook.log', 'resultActivity: ' .print_r($resultActivity, true) . PHP_EOL, FILE_APPEND);
file_put_contents('../../../logs/webhook.log', 'CREATED: ' .print_r(strtotime($resultActivity['result']['CREATED']), true) . PHP_EOL, FILE_APPEND);


if (!empty($resultActivity['result'])) {
    if ($resultActivity['result']['OWNER_TYPE_ID'] == 3)

        $result = CRest::call(
            'crm.contact.update',
            [
                'ID' => $resultActivity['result']['OWNER_ID'],
                'FIELDS' => [
                    'UF_CRM_1766686468932' => date('Y-m-d', strtotime($resultActivity['result']['CREATED'])),
                ],
                'PARAMS' => [
                    'REGISTER_SONET_EVENT' => 'N',
                    'REGISTER_HISTORY_EVENT' => 'N',
                ]
            ]
        );
}
