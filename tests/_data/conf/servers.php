<?php

declare(strict_types=1);

/** @var array{servers: array{name:string, host: string, excluded_databases: string[]}} $configuration */
$configuration = [
    'servers' => [
        [
            'name' => 'Test Box',
            'host' => '127.0.0.1',
            'excluded_databases' => ['mysql', 'sys', 'information_schema', 'performance_schema'],
        ],
    ],
];

return $configuration;
