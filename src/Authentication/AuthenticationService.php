<?php

declare(strict_types=1);

namespace App\Authentication;

use \DateTimeImmutable;
use \Exception;
use Firebase\JWT\Key;
use GeekLab\Conf\GLConf;
use Symfony\Component\HttpFoundation\Request;
use Firebase\JWT\JWT;

class AuthenticationService
{
    private GLConf $conf;

    /** @var object | null $token Decoded JWT object. */
    private ?object $token;

    public function __construct(GLConf $conf)
    {
        $this->conf = $conf;
    }

    /**
     * @throws NotLoggedInException
     */
    public function checkAuthenticated(Request $request): void
    {
        // Get the BEARER AUTH JWT.
        $jwt = explode(' ', $request->server->get('HTTP_AUTHORIZATION'))[1];
        if (!$jwt) {
            throw new NotLoggedInException('NOT LOGGED IN');
        }

        try {
            $now = new DateTimeImmutable();
            $token = JWT::decode($jwt, new Key($this->conf->get('jwt.secret_key'), $this->conf->get('jwt.alg')));
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
