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
            'name' => 'The Box',
            'host' => 'mysql', // Use docker name when possible.
            'port' => 3306,
            'excluded_databases' => ['mysql', 'sys', 'information_schema', 'performance_schema'],
        ],
    ],
];

return $configuration;
