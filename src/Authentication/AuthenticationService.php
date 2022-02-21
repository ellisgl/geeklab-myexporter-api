<?php

declare(strict_types=1);

namespace App\Authentication;

use App\Core\DbService;
use \DateTimeImmutable;
use \Exception;
use Firebase\JWT\Key;
use GeekLab\Conf\GLConf;
use \PDO;
use Symfony\Component\HttpFoundation\Request;
use Firebase\JWT\JWT;

class AuthenticationService
{
    private GLConf $config;

    private DbService $dbService;

    // Decoded JWT object.
    private ?object $token;

    public function __construct(GLConf $config, DbService $dbService)
    {
        $this->config = $config;
        $this->dbService = $dbService;
    }

    /**
     * @param Request $request
     *
     * @return string
     * @throws \JsonException
     */

    public function doAuthentication(Request $request): string
    {
        // Decode the request.
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        // Attempt a connection
        $this->dbService->createPDO(
            $this->config->get('servers.' . (int) $data['host'] . '.host'),
            $data['username'],
            $data['password']
        );

        // Create a JWT token and return it.
        $iat = time();
        return JWT::encode(
            [
                'iss'  => 'localhost',
                'aud'  => 'localhost',
                'iat'  => $iat,
                'nbf'  => $iat,
                'exp'  => $iat + 86400,
                'data' => [
                    'dbh' => $this->config->get('servers.' . (int) $data['host'] . '.host'),
                    'dbu' => $data['username'],
                    'dbp' => $data['password'],
                ]
            ],
            $this->config->get('jwt.secret_key'),
            $this->config->get('jwt.alg')
        );
    }

    /**
     * @throws NotLoggedInException
     */
    public function isAuthenticated(Request $request): void
    {
        // Get the BEARER AUTH JWT.
        $jwt = explode(' ', $request->server->get('HTTP_AUTHORIZATION'))[1];
        if (!$jwt) {
            throw new NotLoggedInException('NOT LOGGED IN');
        }

        try {
            $now = new DateTimeImmutable();
            $token = JWT::decode($jwt, new Key($this->config->get('jwt.secret_key'), $this->config->get('jwt.alg')));
            if ($token->nbf > $now->getTimestamp() || $token->exp < $now->getTimestamp()) {
                throw new NotLoggedInException('NOT LOGGED IN');
            }

            $this->token = $token;
        } catch (Exception $e) {
            throw new NotLoggedInException('NOT LOGGED IN');
        }
    }

    /**
     * Return the decoded JWT object.
     * @return object | null
     */
    public function getToken(): ?object
    {
        return $this->token;
    }
}
