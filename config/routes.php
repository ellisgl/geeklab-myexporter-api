<?php

declare(strict_types=1);

use App\Authentication\AuthenticationController;
use App\Database\DatabaseController;
use App\Database\ServerController;

/**
 * @var array{rautes: array{methods: array<string>, path: string, handler: array<string, string>}} $configuration
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
        // Wildcards need to be at the bottom.
    ],
];

return $configuration;
