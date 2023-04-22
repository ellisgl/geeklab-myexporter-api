<?php

namespace unit\Authentication;

use App\Authentication\AuthenticationService;
use App\Core\Http\Exceptions\HttpUnauthorizedException;
use App\Core\Http\Request;
use App\Database\PdoService;
use Firebase\JWT\JWT;
use GeekLab\Conf\Driver\ArrayConfDriver;
use GeekLab\Conf\GLConf;
use JsonException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PDO;

class AuthenticationServiceTest extends MockeryTestCase
{
    /** @var GLConf $config */
    private GLConf $config;

    /** @var AuthenticationService $service */
    private AuthenticationService $authenticationService;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $confDir = __DIR__ . '/../../_data/conf/';
        $this->config = new GLConf(new ArrayConfDriver($confDir . 'config.php', $confDir));
        $this->config->init();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @return void
     * @throws HttpUnauthorizedException
     * @throws JsonException
     */
    public function testDoAuthentication(): void
    {
        /** @var PdoService|Mockery\LegacyMockInterface|Mockery\MockInterface $dbServiceMock */
        $dbServiceMock = Mockery::mock(PdoService::class . '[createPDO]');
        $dbServiceMock->shouldReceive('createPDO')->once()->andReturns(Mockery::mock(PDO::class));
        $this->createService($dbServiceMock);

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['REMOTE_ADDR' => '127.0.0.1', 'CONTENT-TYPE' => 'application/json'],
            '{"server_id": 0, "username": "test", "password": "password"}'
        );
        $response = $this->authenticationService->doAuthentication($request);
        $this->assertIsString($response);
        $this->assertNotEmpty($response);
    }

    /**
     * @return void
     * @throws HttpUnauthorizedException
     */
    public function testIsAuthenticated(): void
    {
        $dbServiceMock = Mockery::mock(PdoService::class);
        $this->createService($dbServiceMock);

        // Creat a JWT normally.
        $iat = time();
        $jwt = JWT::encode(
            [
                'iss' => 'localhost',
                'aud' => 'localhost',
                'iat' => $iat,
                'nbf' => $iat,
                'exp' => $iat + 86400,
                'hash' => sha1('127.0.0.1'),
                'data' => ['test' => 'testing'],
            ],
            $this->config->get('jwt.secret_key'),
            $this->config->get('jwt.alg'),
        );

        $request = new Request(server: ['REMOTE_ADDR' => '127.0.0.1', 'HTTP_AUTHORIZATION' => 'BEARER: ' . $jwt]);
        $this->authenticationService->isAuthenticated($request);
        $token = $this->authenticationService->getToken();
        $this->assertEquals('testing', $token->data->test);
    }

    /**
     * @param PdoService $dbServiceMock
     *
     * @return void
     */
    private function createService(PdoService $dbServiceMock): void
    {
        $this->authenticationService = new AuthenticationService($this->config, $dbServiceMock);
    }
}
