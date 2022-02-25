<?php

declare(strict_types=1);

use App\Authentication\AuthenticationController;
use App\Database\DatabaseController;
use App\Database\ServerController;

return [
    'routes' => [
        [
            'methods' => ['GET'],
            'path'    => '/servers',
            'handler' => [ServerController::class, 'getServers']
        ],
        [
            'methods' => ['GET'],
            'path'    => '/databases',
            'handler' => [DatabaseController::class, 'getDatabases']
        ],
        [
            'methods' => ['GET'],
            'path'    => '/databases/{database}/tables',
            'handler' => [DatabaseController::class, 'getTables']
        ],
        [
            'methods' => ['POST'],
            'path'    => '/login',
            'handler' => [AuthenticationController::class, 'login']
        ],
        // Wildcards need to be at the bottom.
    ]
];
