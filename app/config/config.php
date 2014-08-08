<?php

$root = __DIR__ . "/../";

return (object)[
    'timezone' => 'Europe/Moscow',
    'schema' => 'http',
    'host' => 'builder.nebo15.dev',
    'api' => [
        'secret' => 'API_SECRET',
    ],
    'admins' => [
        'builder:iospass',
    ],
    'db' => [
        'database' => 'build_server',
    ]
];