<?php

declare(strict_types=1);

return [
    'servers' => [
        [
            'name' => 'The Box',
            'host' => 'mysql', // Use docker name when possible.
            'port' => 3306,
            'excluded_databases' => ['mysql', 'sys', 'information_schema', 'performance_schema'],
        ],
    ],
];
