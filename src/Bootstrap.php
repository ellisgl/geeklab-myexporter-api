<?php

declare(strict_types=1);

namespace App;

error_reporting(E_ALL);

use App\Authentication\AuthenticationInterface;
use App\Authentication\AuthenticationService;
use App\Core\Http\Exceptions\HttpBadRequestException;
use App\Core\Http\Exceptions\HttpMethodNotAllowedException;
use App\Core\Http\Exceptions\HttpNotFoundException;
use App\Core\Http\HttpErrorService;
use App\Core\Http\Request;
use App\Database\PdoService;
use Auryn\ConfigException;
use Auryn\Injector;
use Exception;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use GeekLab\Conf\Driver\ArrayConfDriver;
use GeekLab\Conf\GLConf;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Response;

use function FastRoute\simpleDispatcher;

// Initialize our configuration system.
$config = new GLConf(
    new ArrayConfDriver(__DIR__ . '/../config/config.php', __DIR__ . '/../config/'),
    [],
    ['keys_lower_case']
);
$config->init();
$environment = $config->get('env');

// Create the Request object.
/** @var Request | null $request */
if (!$request) {
    $request = new Request(
        query  : $_GET,
        request: $_POST,
        cookies: $_COOKIE,
        files  : $_FILES,
        server : $_SERVER
    );
}

$errorService = new HttpErrorService();

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

class Bootstrap
{
    private string $environment = 'development';
    private JsonResponse | Response | null $response = null;
    private ?AuthenticationService $authenticationService = null;
    private object | null $jwt = null;

    public function __construct(
        private GLConf $config,
        private Request $request,
        private HttpErrorService $errorService,
        private PdoService $dbService,
        private Injector $injector,
    ) {
    }

    /**
     * Initialize the bootstrap without polluting the constructor with business logic.
     *
     * @param GLConf | null           $config
     * @param Request | null          $request
     * @param HttpErrorService | null $errorService
     * @param PdoService | null       $dbService
     * @param Injector | null         $injector
     *
     * @return self
     */
    public static function initiialize(
        ?GLConf $config = null,
        ?Request $request = null,
        ?HttpErrorService $errorService = null,
        ?PdoService $dbService = null,
        ?Injector $injector = null,
    ): self {
        if (!$config) {
            $config = new GLConf(
                new ArrayConfDriver(__DIR__ . '/../config/config.php', __DIR__ . '/../config/'),
                [],
                ['keys_lower_case']
            );
        }

        if (!$request) {
            $request = new Request(
                query  : $_GET,
                request: $_POST,
                cookies: $_COOKIE,
                files  : $_FILES,
                server : $_SERVER
            );
        }

        if (!$errorService) {
            $errorService = new HttpErrorService();
        }

        if (!$dbService) {
            $dbService = new PdoService();
        }

        if (!$injector) {
            $injector = new Injector();
        }

        return new self(
            $config,
            $request,
            $errorService,
            $dbService,
            $injector
        );
    }

    public function run(): void
    {
        $this->config->init();
        $this->environment = $this->config->get('env') ?? 'development';

        // Create the AuthenticationService object.
        $this->authenticationService = new AuthenticationService($this->config, $this->dbService);

        $this->shareBasicDependencies();
        $this->startPdo();

    }

    private function route(): Response
    {

    }

    /**
     * @return void
     * @throws ConfigException
     */
    private function shareBasicDependencies(): void
    {
        // Create the response object, so we can output to our users.
        $this->injector->share(JsonResponse::class);

        // Create the authentication object, so people can log in.
        $this->injector->share(AuthenticationService::class);

        // Share the Configuration object.
        $this->injector->share($this->config);

        // Share the dbService.
        $this->injector->share($this->dbService);

        // Share the Request object.
        $this->injector->share($this->request);

        // Share the AuthenticationService.
        $this->injector->share($this->authenticationService);
    }

    /**
     * Create PDO connection based on the JWT.
     *
     * @return void
     * @throws ConfigException
     */
    private function startPdo(): void
    {
        // Setup mysql connection for a user that has logged in.
        $this->jwt = $this->authenticationService->getTokenFromRequest($this->request);
        if ($this->jwt) {
            $dbConn = $this->dbService->createPDO(
                $this->jwt->data->dbh,
                $this->jwt->data->dbu,
                $this->jwt->data->dbp,
                $this->jwt->data->port,
            );
            $this->injector->share($dbConn);
        }
    }
}
