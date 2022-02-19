<?php

declare(strict_types=1);

use App\Controllers\DbController;
use App\Authentication\AuthenticationController;

return [
    'routes' => [
        [
            'methods' => ['GET'],
            'path'    => '/db',
            'handler' => [DbController::class, 'index']
        ],
        [
          'methods' => ['GET'],
          'path' => '/db/{database}',
          'handler' => [DbController::class, 'getTables']
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
