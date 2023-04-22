<?php

declare(strict_types=1);

namespace App;

use App\Authentication\AuthenticationInterface;
use App\Authentication\AuthenticationService;
use App\Core\Http\Exceptions\HttpBadRequestException;
use App\Core\Http\Exceptions\HttpMethodNotAllowedException;
use App\Core\Http\Exceptions\HttpNotFoundException;
use App\Core\Http\HttpErrorService;
use App\Core\Http\Request;
use App\Database\PdoService;
use Auryn\Injector;
use Exception;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use GeekLab\Conf\Driver\ArrayConfDriver;
use GeekLab\Conf\GLConf;
use Symfony\Component\HttpFoundation\JsonResponse;

use function FastRoute\simpleDispatcher;

require_once('../vendor/autoload.php');

error_reporting(E_ALL);

// Initialize our configuration system.
$config = new GLConf(
    new ArrayConfDriver(__DIR__ . '/../config/config.php', __DIR__ . '/../config/'),
    [],
    ['keys_lower_case']
);
$config->init();
$environment = $config->get('env');

// Create the Request object.
$request = new Request(query: $_GET, request: $_POST, cookies: $_COOKIE, files: $_FILES, server: $_SERVER);

$errorService = new HttpErrorService();

// Create the DatabaseService object.
$dbService = new PdoService();

// Create the AuthenticationService object.
$authenticationService = new AuthenticationService($config, $dbService);

/** @var JsonResponse | null $response */
$response = null;

try {
    // Configure and init dependency injection.
    /** @var Injector $injector */
    $injector = include_once('Dependencies.php');

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

    // Match the request to a route.
    $routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());

    // Add in some extra case handling and execute the route endpoint.
    $injector->share($errorService);

    switch ($routeInfo[0]) {
        case Dispatcher::METHOD_NOT_ALLOWED:
            throw new HttpMethodNotAllowedException();

        case Dispatcher::FOUND:
            if (is_array($routeInfo[1])) {
                // Controller class and method.
                [$className, $method] = $routeInfo[1];
                $vars = $routeInfo[2];
                try {
                    $class = $injector->make($className);
                } catch (Exception $e) {
                    throw new HttpBadRequestException($e->getMessage(), $e);
                }

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
                throw new HttpBadRequestException('Route not callable.');
            }
            break;

        case Dispatcher::NOT_FOUND:
        default:
            throw new HttpNotFoundException();
    }

    // Output the response to the viewer.
    $response->send();
} catch (Exception $e) {
    if (!$response) {
        $response = new JsonResponse();
    }

    $response = $errorService->handleError($request, $e, $response);
    $response->send();
}
