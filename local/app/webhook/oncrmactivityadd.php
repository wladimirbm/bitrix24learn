<?php
//13w08kkkeoqxqjn60xm41x0emdadivuu

// use App\Debug\Mylog;
// Mylog::addLog($_REQUEST, '$_REQUEST', '', __FILE__, __LINE__);
echo "hello";
file_put_contents('webhook.log', print_r($_REQUEST, true) . PHP_EOL, FILE_APPEND);