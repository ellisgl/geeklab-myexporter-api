<?php

declare(strict_types=1);

namespace App;

error_reporting(E_ALL);

require_once('../constants.php');
require_once(APP_ROOT . '/vendor/autoload.php');

use App\Authentication\AuthenticationInterface;
use App\Authentication\AuthenticationService;
use App\Core\Http\Exceptions\HttpMethodNotAllowedException;
use App\Core\Http\Exceptions\HttpNotFoundException;
use App\Core\Http\HttpErrorService;
use App\Core\Http\Request;
use App\Database\PdoService;
use Auryn\Injector;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use GeekLab\Conf\Driver\ArrayConfDriver;
use GeekLab\Conf\GLConf;
use Symfony\Component\HttpFoundation\JsonResponse;

use Throwable;

use UnexpectedValueException;

use function FastRoute\simpleDispatcher;

// Create the Configuration object if it doesn't exist.
// This allows for "injecting" from the controller tests.
/**  @var GLConf | null $config */
if (!isset($config)) {
    $config = new GLConf(
        new ArrayConfDriver(APP_CFG . '/config.php', APP_CFG . '/'),
        [],
        ['keys_lower_case']
    );
    $config->init();
}

// $environment = $config->get('env');

// Create the Request object if it doesn't exist.
// This allows for "injecting" from the controller tests.
/** @var Request | null $request */
if (!isset($request)) {
    $request = new Request(
        query  : $_GET,
        request: $_POST,
        cookies: $_COOKIE,
        files  : $_FILES,
        server : $_SERVER
    );
}

// Create the HttpErrorService object if it doesn't exist.
// This allows for "injecting" from the controller tests.
/** @var HttpErrorService | null $errorService */
if (!isset($errorService)) {
    $errorService = new HttpErrorService();
}

// Create the DatabaseService object.
$dbService = new PdoService();

// Create the Injector object, so we can do injections.
$injector = new Injector();

// Create the AuthenticationService object.
$authenticationService = new AuthenticationService($config, $dbService);

/** @var JsonResponse | null $response */
$response = null;

try {
    // Create the response object, so we can output to our users.
    $injector->share(JsonResponse::class);

    // Create the authentication object, so people can log in.
    $injector->share(AuthenticationService::class);

    // Share the Configuration object.
    $injector->share($config);

    // Share the dbService.
    $injector->share($dbService);

    // Share the Request object.
    $injector->share($request);

    // Share the AuthenticationService.
    $injector->share($authenticationService);

    // Setup mysql connection for a user that has logged in.
    $jwt = $authenticationService->getTokenFromRequest($request);
    if ($jwt) {
        $dbConn = $dbService->createPDO($jwt->data->dbh, $jwt->data->dbu, $jwt->data->dbp, $jwt->data->port);
        $injector->share($dbConn);
    }

    // Routing code:
    // Load up routes for router, and initialize the dispatcher.
    $routeDefinitionCallback = static function (RouteCollector $r) use ($config) {
        foreach ($config->get('routes') as $route) {
            $r->addRoute($route['methods'], $route['path'], $route['handler']);
        }
    };
    $dispatcher = simpleDispatcher($routeDefinitionCallback);

    // Add in some extra case handling and execute the route endpoint.
    $injector->share($errorService);

    // Match the request to a route.
    $routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());

    switch ($routeInfo[0]) {
        case Dispatcher::METHOD_NOT_ALLOWED:
            throw new HttpMethodNotAllowedException();

        case Dispatcher::FOUND:
            if (is_array($routeInfo[1])) {
                // Controller class and method.
                [$className, $method] = $routeInfo[1];
                $vars = $routeInfo[2];
                $class = $injector->make($className);

                // We'll do a middleware the manual way,
                // instead of the PSR-15 way until I find something better.
                // If the controller class implements the Authentication interface, do an authentication check.
                if (in_array(AuthenticationInterface::class, class_implements($class), true)) {
                    $authenticationService->isAuthenticated($request);
                }

                // Execute the action method.
                $response = $class->$method($vars);
            } elseif (is_callable($routeInfo[1])) {
                // Closure endpoint.
                $response = $injector->make(JsonResponse::class);
                $response->setContent($injector->execute($routeInfo[1]));
            } else {
                // We have something bad here.
                throw new UnexpectedValueException('Invalid type for route handler.');
            }
            break;

        default:
            throw new HttpNotFoundException();
    }

    // Output the response to the viewer.
    $response->send();
} catch (Throwable $e) {
    if (!$response) {
        $response = new JsonResponse();
    }

    $response = $errorService->handleError($request, $e, $response);
    $response->send();
}
