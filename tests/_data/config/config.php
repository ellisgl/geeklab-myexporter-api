<?php

declare(strict_types=1);

/**
 * @var array{enviroment: string, conf: string[] | array} $configuration
 */
$configuration = [
    'environment' => 'test',
    'conf' => [
        'routes',
        'servers',
        'jwt',
    ],
];

return $configuration;
