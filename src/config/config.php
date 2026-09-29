<?php
return [
    'app_name' => getenv('APP_NAME') ?: 'Eder Informática',
    'db' => [
        'host' => getenv('DB_HOST') ?: 'db',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'eder_informatica',
        'user' => getenv('DB_USER') ?: 'eder',
        'pass' => getenv('DB_PASS') ?: 'eder123',
    ],
];
