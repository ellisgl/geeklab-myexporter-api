<?php

declare(strict_types=1);

/**
 * @var array{environment: string, conf: string[]} $configuration
 */
$configuration = [
    'environment' => 'dev',
    'conf' => [
        'routes',
        'servers',
        'jwt',
    ],
];

return $configuration;
