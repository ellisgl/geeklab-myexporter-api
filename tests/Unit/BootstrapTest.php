<?php

namespace Test\Unit;

use Symfony\Component\HttpFoundation\Response;

/**
 * Test other things not covered by controller tests.
 */
class BootstrapTest extends ControllerTestCase
{
    public function testRouterAnonymousFunctionHandler(): void
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/hello';
        // No need to create the Request object here, the bootstrap can handle this one for us.

        // Inject the config object.
        $config = self::$config;

        // Change to src directory, so Bootstrap.php can find its includes,
        chdir(APP_ROOT . '/src');
        include(APP_ROOT . '/src/Bootstrap.php');

        /** @var Response $response */
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('Hello World!', $response->getContent());
    }

    public function testBadClassName(): void
    {
        // Set the temporary file (stream) to store error_log entries in (Ignore it).
        $errorLogTmpFile = tmpFile();
        $errorLogLocationBackup = ini_set('error_log', stream_get_meta_data($errorLogTmpFile)['uri']);

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/bad-class';
        // No need to create the Request object here, the bootstrap can handle this one for us.

        // Inject the config object.
        $config = self::$config;

        // Change to src directory, so Bootstrap.php can find its includes,
        chdir(APP_ROOT . '/src');
        include(APP_ROOT . '/src/Bootstrap.php');

        /** @var Response $response */
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());

        // Set error_log back to the default.
        ini_set('error_log', $errorLogLocationBackup);
    }

    public function testBadMethod(): void
    {
        // Set the temporary file (stream) to store error_log entries in (Ignore it).
        $errorLogTmpFile = tmpFile();
        $errorLogLocationBackup = ini_set('error_log', stream_get_meta_data($errorLogTmpFile)['uri']);

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/bad-method';
        // No need to create the Request object here, the bootstrap can handle this one for us.

        // Inject the config object.
        $config = self::$config;

        // Change to src directory, so Bootstrap.php can find its includes,
        chdir(APP_ROOT . '/src');
        include(APP_ROOT . '/src/Bootstrap.php');

        /** @var Response $response */
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());

        // Set error_log back to the default.
        ini_set('error_log', $errorLogLocationBackup);
    }

    public function testBadHandlerType(): void
    {
        // Set the temporary file (stream) to store error_log entries in (Ignore it).
        $errorLogTmpFile = tmpFile();
        $errorLogLocationBackup = ini_set('error_log', stream_get_meta_data($errorLogTmpFile)['uri']);

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/bad-handler-type';
        // No need to create the Request object here, the bootstrap can handle this one for us.

        // Inject the config object.
        $config = self::$config;

        // Change to src directory, so Bootstrap.php can find its includes,
        chdir(APP_ROOT . '/src');
        include(APP_ROOT . '/src/Bootstrap.php');

        /** @var Response $response */
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());

        // Set error_log back to the default.
        ini_set('error_log', $errorLogLocationBackup);
    }

    public function testNotFound(): void
    {
        // Set the temporary file (stream) to store error_log entries in (Ignore it).
        $errorLogTmpFile = tmpFile();
        $errorLogLocationBackup = ini_set('error_log', stream_get_meta_data($errorLogTmpFile)['uri']);

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/not-found';
        // No need to create the Request object here, the bootstrap can handle this one for us.

        // Inject the config object.
        $config = self::$config;

        // Change to src directory, so Bootstrap.php can find its includes,
        chdir(APP_ROOT . '/src');
        include(APP_ROOT . '/src/Bootstrap.php');

        /** @var Response $response */
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        // Set error_log back to the default.
        ini_set('error_log', $errorLogLocationBackup);
    }

    public function testRealConfig(): void
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/servers';
        // No need to create the Request object here, the bootstrap can handle this one for us.

        // Change to src directory, so Bootstrap.php can find its includes,
        chdir(APP_ROOT . '/src');
        include(APP_ROOT . '/src/Bootstrap.php');

        /** @var Response $response */
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

    }
}
