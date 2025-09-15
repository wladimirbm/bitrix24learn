<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php 
use App\Diag\Mylog;
App\Diag\Mylog::ClearDefLog();
LocalRedirect('/otus/homework2/index.php');
?>