<?php

namespace Test\Unit;

use App\Core\Http\HttpErrorService;
use App\Core\Http\Request;
use Faker\Factory;
use Faker\Generator;
use Firebase\JWT\JWT;
use GeekLab\Conf\Driver\ArrayConfDriver;
use GeekLab\Conf\GLConf;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

require_once(__DIR__ . '/../../constants.php');

abstract class ControllerTestCase extends TestCase
{
    protected static ?string $jwt = null;
    protected static ?GLConf $config = null;

    protected ?LoggerInterface $logger = null;
    protected ?HttpErrorService $httpErrorService = null;
    protected ?Generator $faker = null;

    public static function setUpBeforeClass(): void
    {
        if (!self::$config) {
            self::$config = new GLConf(
                new ArrayConfDriver(TEST_CFG . '/config.php', TEST_CFG . '/'),
                [],
                ['keys_lower_case']
            );
            self::$config->init();
        }

        if (!self::$jwt) {
            // Create a JWT token and return it.
            $iat = time();

            self::$jwt = JWT::encode(
                [
                    'iss' => 'localhost',
                    'aud' => 'myExporter: ' . self::$config->get('servers.0.name'),
                    'iat' => $iat,
                    'nbf' => $iat,
                    'exp' => $iat + 86400,
                    'hash' => sha1('127.0.0.1'),
                    'data' => [
                        'host' => 0,
                        'dbh' => self::$config->get('servers.0.host'),
                        'dbu' => 'root',
                        'dbp' => 'root',
                        'port' => self::$config->get('servers.0.port') ?: 3306,
                    ],
                ],
                self::$config->get('jwt.secret_key'),
                self::$config->get('jwt.alg'),
            );
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = $this
            ->getMockBuilder(LoggerInterface::class)
            ->onlyMethods(
                [
                    'emergency',
                    'alert',
                    'critical',
                    'error',
                    'warning',
                    'notice',
                    'info',
                    'debug',
                    'log',
                ],
            )
            ->getMock();
        $this->httpErrorService = new HttpErrorService($this->logger);

        // Use the factory to create a Faker\Generator instance.
        $this->faker = Factory::create();
    }

    /**
     * Create the Request Object in a DRY way.
     *
     * @param string        $remoteAddr
     * @param string        $method
     * @param string        $uri
     * @param string | null $content
     *
     * @return Request
     */
    public function createRequestObject(
        string $remoteAddr,
        string $method,
        string $uri,
        ?string $content = null,
    ): Request {
        $_SERVER['REMOTE_ADDR'] = $remoteAddr;
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . self::$jwt;

        return new Request(
            query  : $_GET,
            request: $_POST,
            cookies: $_COOKIE,
            files  : $_FILES,
            server : $_SERVER,
            content: $content
        );
    }
}
