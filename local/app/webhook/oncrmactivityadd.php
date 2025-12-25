<?php
//13w08kkkeoqxqjn60xm41x0emdadivuu

// use App\Debug\Mylog;
// Mylog::addLog($_REQUEST, '$_REQUEST', '', __FILE__, __LINE__);
//echo "hello";
file_put_contents('../../../logs/webhook.log', print_r($_REQUEST, true) . PHP_EOL, FILE_APPEND);
//https://cc61466.tw1.ru/rest/1/kuzuzl8fna81k2n0/