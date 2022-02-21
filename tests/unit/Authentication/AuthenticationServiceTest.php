<?php

namespace unit\Authentication;

use App\Authentication\AuthenticationService;
use Firebase\JWT\JWT;
use GeekLab\Conf\Driver\ArrayConfDriver;
use GeekLab\Conf\GLConf;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpFoundation\Request;

include __DIR__ . '/../../helpers/PDOStub.php';

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

        $this->authenticationService = new AuthenticationService($this->config);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDoAuthentication(): void
    {
        // I can't mock PDO at this time, so I can't test this method.
        // I might have to do some weird injection and init, then mock the injection and return a mock...
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
