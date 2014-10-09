<?php

$root = __DIR__ . "/../";

return [
    'timezone' => 'Europe/Moscow',
    'schema' => 'https',
    'host' => 'builder.nebo15.dev',
    'users' => [
        'john' => 'smith',
    ],
    'api_keys' => [
        'randomapp' => 'ThisIsSecretKey',
    ],
    'db' => [
        'database' => 'build_server',
    ]
];
