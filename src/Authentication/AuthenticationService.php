<?php

declare(strict_types=1);

namespace App\Authentication;

use App\Core\Http\Exceptions\HttpUnauthorizedException;
use App\Core\Request;
use App\Database\PdoService;
use DateTimeImmutable;
use \Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GeekLab\Conf\GLConf;
use \JsonException;

class AuthenticationService
{
    private GLConf $config;

    private PdoService $dbService;

    // Decoded JWT object.
    private ?object $token = null;

    public function __construct(GLConf $config, PdoService $dbService)
    {
        $this->config = $config;
        $this->dbService = $dbService;
    }

    /**
     * @param Request $request
     *
     * @return string
     * @throws JsonException
     * @throws HttpUnauthorizedException
     */

    public function doAuthentication(Request $request): string
    {
        // Decode the request.
        $data = $request->getJsonContentAsArray();

        // Attempt a connection
        try {
            $this->dbService->createPDO(
                $this->config->get('servers.' . (int) $data['server_id'] . '.host'),
                $data['username'],
                $data['password']
            );
        } catch (\Exception $e) {
            throw new HttpUnauthorizedException();
        }

        // Create a JWT token and return it.
        $iat = time();
        return JWT::encode(
            [
                'iss' => 'localhost',
                'aud' => 'myExporter: ' . $this->config->get('servers.' . (int) $data['server_id'] . '.name'),
                'iat' => $iat,
                'nbf' => $iat,
                'exp' => $iat + 86400,
                'hash' => sha1($request->getClientIp()),
                'data' => [
                    'host' => (int) $data['host'],
                    'dbh'  => $this->config->get('servers.' . (int) $data['server_id'] . '.host'),
                    'dbu'  => $data['username'],
                    'dbp'  => $data['password'],
                    'port' => $this->config->get('servers.' . (int) $data['server_id'] . '.port') ?: 3306,
                ]
            ],
            $this->config->get('jwt.secret_key'),
            $this->config->get('jwt.alg')
        );
    }

    /**
     * @throws HttpUnauthorizedException
     */
    public function isAuthenticated(Request $request): void
    {
        // Get the BEARER AUTH JWT.
        if (!$this->getTokenFromRequest($request)) {
            throw new HttpUnauthorizedException('NOT LOGGED IN');
        }
    }

    /**
     * Return the decoded JWT object.
     *
     * @return object | null
     */
    public function getToken(): ?object
    {
        return $this->token;
    }

    /**
     * @param Request $request
     *
     * @return object | null
     */
    public function getTokenFromRequest(Request $request): ?object
    {
        if ($request->server->get('HTTP_AUTHORIZATION')) {
            $auth = explode(' ', $request->server->get('HTTP_AUTHORIZATION'));
            if (!empty($auth[1])) {
                $jwt = $auth[1];

                try {
                    $now = new DateTimeImmutable();
                    $token = JWT::decode(
                        $jwt,
                        new Key($this->config->get('jwt.secret_key'), $this->config->get('jwt.alg'))
                    );
                    $hash = sha1($request->getClientIp());
                    $this->token = $token->nbf > $now->getTimestamp() || $token->exp < $now->getTimestamp(
                    ) || $token->hash !== $hash
                        ? null
                        : $token;
                } catch (Exception $e) {
                    $this->token = null;
                }
            }
        }

        return $this->token;
    }
}
