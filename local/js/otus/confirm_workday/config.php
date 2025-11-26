<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

return [
    'js' => [
        '/local/js/otus/confirm_workday/main.js',
    ],
    'rel' => [
        'ui.dialogs.messagebox',
        'main.core',
    ],
    'skip_core' => false,
];