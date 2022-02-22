<?php

declare(strict_types=1);

use App\Authentication\AuthenticationController;
use App\Database\DatabaseController;

return [
    'routes' => [
        [
            'methods' => ['GET'],
            'path'    => '/db',
            'handler' => [DatabaseController::class, 'index']
        ],
        [
          'methods' => ['GET'],
          'path' => '/db/{database}',
          'handler' => [DatabaseController::class, 'getTables']
        ],
        [
            'methods' => ['GET'],
            'path'    => '/logout',
            'handler' => [AuthenticationController::class, 'logout']
        ],
        [
            'methods' => ['GET', 'POST'],
            'path'    => '/',
            'handler' => [AuthenticationController::class, 'login']
        ],
        // Wildcards need to be at the bottom.
    ]
];
