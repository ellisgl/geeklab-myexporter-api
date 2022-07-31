<?php

declare(strict_types=1);

namespace App;

use App\Authentication\AuthenticationInterface;
use App\Authentication\AuthenticationService;
use App\Core\Http\HttpErrorService;
use App\Core\Request;
use App\Database\PdoService;
use Auryn\Injector;
use \Exception;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use GeekLab\Conf\Driver\ArrayConfDriver;
use GeekLab\Conf\GLConf;
use Symfony\Component\HttpFoundation\JsonResponse;

use function FastRoute\simpleDispatcher;

require __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);

// Initialize our configuration system.
$config = new GLConf(
    new ArrayConfDriver(__DIR__ . '/../config/config.php', __DIR__ . '/../config/'),
    [],
    ['keys_lower_case']
);
$config->init();
$environment = $config->get('env');

// Create the DatabaseService object.
$dbService = new PdoService();

// Configure and init dependency injection.
/** @var Injector $injector */
$injector = include('Dependencies.php');
/** @var Request $request */
$request = $injector->make(Request::class);

// Share the configuration with the rest of the system.
$injector->share($config);

// Share the dbService.
$injector->share($dbService);

// Make sure we have authentication system created.
/** @var AuthenticationService $authenticationService */
$authenticationService = $injector->make(AuthenticationService::class);

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
$errorService = $injector->make(HttpErrorService::class);

/** @var JsonResponse $response */
switch ($routeInfo[0]) {
    case Dispatcher::METHOD_NOT_ALLOWED:
        $response = $errorService->error405();
        break;
    case Dispatcher::FOUND:
        try {
            if (is_array($routeInfo[1])) {
                // Controller class and method.
                [$className, $method] = $routeInfo[1];
                $vars = $routeInfo[2];
                $class = $injector->make($className);

                // We'll do a middleware the manual way, instead of the PSR-15 way for now, till I find something better.
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
                $response = $errorService->error405();
            }
        } catch (Exception $e) {
            $response = $errorService->handleError($e);
        }
        break;
    case Dispatcher::NOT_FOUND:
    default:
        $response = $errorService->error404();
        break;
}

// Output the response to the viewer.
$response->send();
