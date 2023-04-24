<?php

declare(strict_types=1);

use App\Authentication\AuthenticationController;
use App\Database\DatabaseController;
use App\Database\ServerController;

/**
 * @var array{
 *          servers: array{
 *              name:string,
 *              host: string,
 *              port: int | null,
 *              excluded_databases: string[] | array
 *          }
 *       } $configuration
 */
$configuration = [
    'routes' => [
        [
            'methods' => ['GET'],
            'path' => '/servers',
            'handler' => [ServerController::class, 'getServers'],
        ],
        [
            'methods' => ['GET'],
            'path' => '/databases',
            'handler' => [DatabaseController::class, 'getDatabases'],
        ],
        [
            'methods' => ['GET'],
            'path' => '/databases/{database}/tables',
            'handler' => [DatabaseController::class, 'getTables'],
        ],
        [
            'methods' => ['POST'],
            'path' => '/login',
            'handler' => [AuthenticationController::class, 'login'],
        ],
        [
            'methods' => ['GET'],
            'path' => '/hello',
            'handler' => function () {
                return 'Hello World!';
            }
        ],
        // Error causing routes.
        [
            'methods' => ['GET'],
            'path' => '/bad-class',
            'handler' => ['badClass', 'getStuff'],
        ],
        [
            'methods' => ['GET'],
            'path' => '/bad-method',
            'handler' => [ServerController::class, 'getStuff'],
        ],
        [
            'methods' => ['GET'],
            'path' => '/bad-handler-type',
            'handler' => 666,
        ],
        // Wildcards need to be at the bottom.
    ],
];

return $configuration;
