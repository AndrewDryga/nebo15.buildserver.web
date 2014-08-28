<?php

$root = __DIR__ . "/../";

return [
    'timezone' => 'Europe/Moscow',
    'schema' => 'http',
    'host' => 'builder.nebo15.dev',
    'api' => [
        // app_id => app_secret
        'APP_ID' => 'APP_SECRET',
    ],
    'admins' => [
        'builder:iospass',
    ],
    'db' => [
        'database' => 'build_server',
    ]
];