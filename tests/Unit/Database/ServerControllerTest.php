<?php

namespace Test\Unit\Database;

use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\Response;
use Test\Unit\ControllerTestCase;

class ServerControllerTest extends ControllerTestCase
{
    /**
     * @return void
     * @throws GuzzleException
     */
    public function testGetServers(): void
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/servers';
        // No need to create the Request object here, the bootstrap can handle this one for us.

        // Change to src directory, so Bootstrap.php can find its includes,
        chdir(APP_ROOT . '/src');
        include(APP_ROOT . '/src/Bootstrap.php');

        /** @var Response $response */
        $contents = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertCount(2, $contents);
        $this->assertEquals(self::$config->get('servers.0.name'), $contents[0]['name']);
        $this->assertEquals(self::$config->get('servers.1.name'), $contents[1]['name']);
    }
}
