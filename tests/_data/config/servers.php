<?php

declare(strict_types=1);

/** @var array{
 *          servers: array{
 *              name:string,
 *              host: string,
 *              port: int | null,
 *              excluded_databases: string[] | array
 *          }
 *       } $configuration
 */
$configuration = [
    'servers' => [
        [
            'name' => 'Test Box',
            'host' => '0.0.0.0', // Unit testing from local doesn't work with docker name resolution.
            'port' => 8306,
            'excluded_databases' => ['mysql', 'sys', 'information_schema', 'performance_schema'],
        ],
    ],
];

return $configuration;
