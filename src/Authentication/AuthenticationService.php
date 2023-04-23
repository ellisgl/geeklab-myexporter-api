<?php

declare(strict_types=1);

namespace App\Authentication;

use App\Core\Http\Exceptions\HttpUnauthorizedException;
use App\Core\Http\Request;
use App\Database\PdoService;
use DateTimeImmutable;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GeekLab\Conf\GLConf;
use JsonException;

class AuthenticationService
{
    // Decoded JWT object.
    private ?object $token = null;

    public function __construct(private readonly GLConf $config, private readonly PdoService $dbService)
    {
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

        // Attempt an authenticated connection to the database.
        try {
            $this->dbService->createPDO(
                $this->config->get('servers.' . (int)$data['server_id'] . '.host'),
                $data['username'],
                $data['password'],
                $this->config->get('servers.' . (int)$data['server_id'] . '.port') ?: 3306,
            );
        } catch (Exception) {
            throw new HttpUnauthorizedException();
        }

        // Create a JWT token and return it.
        $iat = time();

        return JWT::encode(
            [
                'iss' => 'localhost',
                'aud' => 'myExporter: ' . $this->config->get('servers.' . (int)$data['server_id'] . '.name'),
                'iat' => $iat,
                'nbf' => $iat,
                'exp' => $iat + 86400,
                'hash' => sha1($request->getClientIp()),
                'data' => [
                    'host' => (int)$data['server_id'],
                    'dbh' => $this->config->get('servers.' . (int)$data['server_id'] . '.host'),
                    'dbu' => $data['username'],
                    'dbp' => $data['password'],
                    'port' => $this->config->get('servers.' . (int)$data['server_id'] . '.port') ?: 3306,
                ],
            ],
            $this->config->get('jwt.secret_key'),
            $this->config->get('jwt.alg'),
        );
    }

    /**
     * Check if the request is authenticated.
     *
     * @param Request $request
     *
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
     * Return the decoded JWT object.
     *
     * @param Request $request
     *
     * @return object | null
     */
    public function getTokenFromRequest(Request $request): ?object
    {
        $token = $request->server->get('HTTP_AUTHORIZATION') ?: $request->server->get('REDIRECT_HTTP_AUTHORIZATION');
        if ($token) {
            $auth = explode(' ', $token);
            if (!empty($auth[1])) {
                $jwt = $auth[1];

                try {
                    $now = new DateTimeImmutable();
                    $token = JWT::decode(
                        $jwt,
                        new Key($this->config->get('jwt.secret_key'), $this->config->get('jwt.alg')),
                    );
                    $hash = sha1($request->getClientIp());
                    $this->token =
                        $token->nbf > $now->getTimestamp() ||
                        $token->exp < $now->getTimestamp() ||
                        $token->hash !== $hash
                            ? null
                            : $token;
                } catch (Exception) {
                    $this->token = null;
                }
            }
        }

        return $this->token;
    }
}
