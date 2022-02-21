<?php

namespace unit\Authentication;

use App\Authentication\AuthenticationService;
use App\Core\DbService;
use Firebase\JWT\JWT;
use GeekLab\Conf\Driver\ArrayConfDriver;
use GeekLab\Conf\GLConf;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PDO;
use Symfony\Component\HttpFoundation\Request;

class AuthenticationServiceTest extends MockeryTestCase
{
    /** @var GLConf $config */
    private GLConf $config;

    /** @var AuthenticationService $service */
    private AuthenticationService $authenticationService;

    protected function setUp(): void
    {
        parent::setUp();
        $confDir = __DIR__ . '/../../_data/conf/';
        $this->config = new GLConf(new ArrayConfDriver($confDir . 'config.php', $confDir));
        $this->config->init();
        $dbServiceMock = Mockery::mock(DbService::class.'[createPDO]');
        $dbServiceMock->shouldReceive('createPDO')->once()->andReturns(Mockery::mock(PDO::class));
        $this->authenticationService = new AuthenticationService($this->config, $dbServiceMock);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDoAuthentication(): void
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['CONTENT-TYPE' => 'application/json'],
            '{"host": 0, "username": "test", "password": "password"}'
        );
        $response = $this->authenticationService->doAuthentication($request);
        var_dump($response);
        $this->assertTrue(true);
    }

    public function testIsAuthenticated(): void
    {
        // Creat a JWT normally
        $iat = time();
        $jwt = JWT::encode(
            [
                'iss'  => 'localhost',
                'aud'  => 'localhost',
                'iat'  => $iat,
                'nbf'  => $iat,
                'exp'  => $iat + 86400,
                'data' => ['test' => 'testing']
            ],
            $this->config->get('jwt.secret_key'),
            $this->config->get('jwt.alg')
        );

        $request = new Request([],[],[],[],[], ['HTTP_AUTHORIZATION' => 'BEARER: ' . $jwt]);
        $this->authenticationService->isAuthenticated($request);
        $token = $this->authenticationService->getToken();
        $this->assertEquals('testing', $token->data->test);
    }
}
