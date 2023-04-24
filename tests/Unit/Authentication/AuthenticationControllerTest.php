<?php

namespace Test\Unit\Authentication;

use App\Authentication\AuthenticationController;
use App\Authentication\AuthenticationService;
use App\Core\Http\Exceptions\HttpMethodNotAllowedException;
use App\Core\Http\Exceptions\HttpUnauthorizedException;
use App\Database\PdoService;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Test\Unit\ControllerTestCase;

class AuthenticationControllerTest extends ControllerTestCase
{
    /**
     * Test that we can successfully log in.
     *
     * @return void
     * @throws GuzzleException
     */
    public function testLogin(): void
    {
        // Inject some override objects.
        $config = self::$config;
        $request = $this->createRequestObject(
            '127.0.0.1',
            'POST',
            '/login',
            json_encode(['server_id' => 0, 'username' => 'root', 'password' => 'root']),
        );

        // Change to src directory, so Bootstrap.php can find its includes,
        chdir(APP_ROOT . '/src');
        include(APP_ROOT . '/src/Bootstrap.php');

        /** @var Response $response */
        $contents = json_decode($response->getContent(), true);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertNotEmpty($contents['jwt']);
        $this->assertEquals('success', $contents['message']);
    }

    /**
     * Test that when we try to pass in a wrong HTTP method, we get a 405.
     *
     * @return void
     */
    public function testLoginWithWrongHttpMethod(): void
    {
        // Inject some override objects.
        $config = self::$config;
        $request = $this->createRequestObject('127.0.0.1', 'GET', '/login');

        // Change to src directory, so Bootstrap.php can find its includes,
        chdir(APP_ROOT . '/src');
        include(APP_ROOT . '/src/Bootstrap.php');

        /** @var Response $response */
        $this->assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
    }

    /**
     * Test for HttpMethodNotAllowedException being thrown from controller method directly.
     *
     * @return void
     * @throws HttpMethodNotAllowedException
     * @throws HttpUnauthorizedException
     * @throws JsonException
     */
    public function testLoginWithWrongHttpMethodDirect(): void
    {
        // Inject some override objects.
        $config = self::$config;
        $request = $this->createRequestObject('127.0.0.1', 'GET', '/login');
        $authenticationService = new AuthenticationService(self::$config, new PdoService());
        $controller = new AuthenticationController(self::$config, $request, new JsonResponse(),  $authenticationService);

        $this->expectException(HttpMethodNotAllowedException::class);
        $controller->login();
    }
}
