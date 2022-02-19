<?php

declare(strict_types=1);

return [
    'servers' => [
        [
            'name' => 'The Box',
            'host' => '127.0.0.1',
            'excluded_databases' => ['mysql', 'sys', 'information_schema', 'performance_schema'],
        ],
    ]
];
