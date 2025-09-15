<?php

return array(
    'exception_handling' =>
    array(
        'value' =>
        array(
            'debug' => true,
            'handled_errors_types' => E_ALL & ~E_WARNING & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE & ~E_DEPRECATED,
            'exception_errors_types' => E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE & ~E_DEPRECATED,
            'ignore_silence' => false,
            'assertion_throws_exception' => true,
            'assertion_error_type' => 256,
            'log' => array(
                'required_file' => '/local/php_interface/src/iag/fileexceptionhandlerlogcustom.php',
                'settings' => array(
                    'file' => '/logs/exception_' . date('Y-m-d') . ".log",
                    'log_size' => 1000000,
                )

            ),
        ),
        'readonly' => false,
    ),
);
