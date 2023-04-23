<?php

namespace Test\Unit\Database;

use Symfony\Component\HttpFoundation\Response;
use Test\Unit\ControllerTestCase;

class DatabaseControllerTest extends ControllerTestCase
{
    public function testGetDatabases(): void
    {
        $request = $this->createRequestObject('127.0.0.1', 'GET', '/databases');
        // Change to src directory, so Bootstrap.php can find its includes,
        chdir(APP_ROOT . '/src');
        include(APP_ROOT . '/src/Bootstrap.php');

        /** @var Response $response */
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $contents = json_decode($response->getContent(), true);
        $this->assertCount(1, $contents);
    }

    public function testGetTables(): void
    {
        $request = $this->createRequestObject('127.0.0.1', 'GET', '/databases/test/tables');
        // Change to src directory, so Bootstrap.php can find its includes,
        chdir(APP_ROOT . '/src');
        $this->expectOutputRegex('/\[{"table":"tblA","size":\d+},{"table":"tblB","size":\d+}\]/');
        include(APP_ROOT . '/src/Bootstrap.php');

        /** @var Response $response */
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testGetTablesFromExcludedDatabase(): void
    {
        // Set the temporary file (stream) to store error_log entries in (Ignore it).
        $errorLogTmpFile = tmpFile();
        $errorLogLocationBackup = ini_set('error_log', stream_get_meta_data($errorLogTmpFile)['uri']);

        $request = $this->createRequestObject('127.0.0.1', 'GET', '/databases/mysql/tables');
        // Change to src directory, so Bootstrap.php can find its includes,

        $this->expectOutputString('Bad Request');
        chdir(APP_ROOT . '/src');
        include(APP_ROOT . '/src/Bootstrap.php');

        /** @var Response $response */
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        // Set error_log back to the default.
        ini_set('error_log', $errorLogLocationBackup);
    }

    public function testGetTablesFromNonExistingDatabase(): void
    {
        // Set the temporary file (stream) to store error_log entries in (Ignore it).
        $errorLogTmpFile = tmpFile();
        $errorLogLocationBackup = ini_set('error_log', stream_get_meta_data($errorLogTmpFile)['uri']);

        $request = $this->createRequestObject('127.0.0.1', 'GET', '/databases/TableOfAwesome/tables');
        // Change to src directory, so Bootstrap.php can find its includes,

        $this->expectOutputString('Bad Request');
        chdir(APP_ROOT . '/src');
        include(APP_ROOT . '/src/Bootstrap.php');

        /** @var Response $response */
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        // Set error_log back to the default.
        ini_set('error_log', $errorLogLocationBackup);
    }
}
