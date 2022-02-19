<?php

namespace unit\Authentication;

use App\Authentication\AuthenticationService;
use Firebase\JWT\JWT;
use GeekLab\Conf\Driver\ArrayConfDriver;
use GeekLab\Conf\GLConf;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class AuthenticationServiceTest extends TestCase
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

    public function testDoAuthentication(): void
    {
        $request = new Request([],[]);
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
