<?php

declare(strict_types=1);

use App\Authentication\AuthenticationService;
use Auryn\Injector;
use Symfony\Component\HttpFoundation\JsonResponse;

// Create the injector.
$injector = new Injector();

// Create the response object, so we can output to our users.
$injector->share(JsonResponse::class);

// Create the authentication object, so people can log in.
$injector->share(AuthenticationService::class);

// Return the injector, so it can be used to inject our goodies.
return $injector;
