<?php

declare(strict_types=1);

use App\Authentication\AuthenticationService;
use App\Core\Http\Request;
use App\Exception\KablooeyException;
use Auryn\Injector;
use Symfony\Component\HttpFoundation\JsonResponse;

try {
    // Create the injector.
    $injector = new Injector();

    // Create the request object, so we can share it with our controller actions.
    $injector->share(Request::class);
    $injector->define(
        Request::class,
        [
            ':query' => $_GET,
            ':request' => $_POST,
            ':attributes' => [],
            ':cookies' => $_COOKIE,
            ':files' => $_FILES,
            ':server' => $_SERVER,
        ]
    );

    // Create the response object, so we can output to our users.
    $injector->share(JsonResponse::class);

    // Create the authentication object, so people can log in.
    $injector->share(AuthenticationService::class);

    // Return the injector, so it can be used to inject our goodies.
    return $injector;
} catch (Exception $e) {
    throw new KablooeyException($e->getMessage(), $e->getCode(), $e);
}
