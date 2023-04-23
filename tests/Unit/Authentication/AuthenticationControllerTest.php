<?php

namespace Test\Unit\Authentication;

use App\Core\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\Response;
use Test\Unit\ControllerTestCase;

class AuthenticationControllerTest extends ControllerTestCase
{
    public function testLoginWithWrongHttpMethod(): void
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/login';
        $request = new Request(
            query  : $_GET,
            request: $_POST,
            cookies: $_COOKIE,
            files  : $_FILES,
            server : $_SERVER
        );

        // Change to src directory, so Bootstrap.php can find its includes,
        chdir(APP_ROOT . '/src');
        include(APP_ROOT . '/src/Bootstrap.php');
        /** @var Response $response */
        $this->assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
    }

    /**
     * @return void
     * @throws GuzzleException
     */
    public function testLogin(): void
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/login';
        $request = new Request(
            query  : $_GET,
            request: $_POST,
            cookies: $_COOKIE,
            files  : $_FILES,
            server : $_SERVER,
            content: json_encode(['server_id' => 1, 'username' => 'root', 'password' => 'root'])
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
}
