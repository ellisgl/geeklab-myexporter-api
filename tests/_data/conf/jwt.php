<?php

declare(strict_types=1);

/**
 * @var array{ jwt: array{ secret_key: string, alg: string }} $configuration
 */
$configuration = [
    'jwt' => [
        'secret_key' => 'My Awesome Secret Key',
        'alg' => 'HS256',
    ],
];

return $configuration;
