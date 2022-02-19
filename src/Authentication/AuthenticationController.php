<?php

declare(strict_types=1);

namespace App\Authentication;

use App\Core\BaseController;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use \JsonException;
use \PDO;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationController extends BaseController
{
    /**
     * Perform login.
     *
     * @Todo: Move logic to service or something.
     *
     * @return JsonResponse
     */
    /**
     * @return JsonResponse
     * @throws JsonException
     */
    public function login(): JsonResponse
    {
        if (Request::METHOD_POST !== $this->request->getMethod()) {
            throw new BadRequestException();
        }

        // Decode the request.
        $data = json_decode($this->request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // Attempt a connection
        $pdo = new PDO(
            'mysql:host=' . $this->config->get('servers')[(int) $data['host']]['host'] . ';',
            $data['username'],
            $data['password'],
            [PDO::ATTR_PERSISTENT => false]
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create a JWT token and return it.
        $iat = time();
        $this->response->setData(
            [
                'message' => 'Successful',
                'jwt' => JWT::encode(
                    [
                        'iss'  => 'localhost',
                        'aud'  => 'localhost',
                        'iat'  => $iat,
                        'nbt'  => $iat,
                        'exp'  => $iat + 86400,
                        'data' => [
                            'dbh' => $this->config->get('servers')[(int) $data['host']]['host'],
                            'dbu' => $data['username'],
                            'dbp' => $data['password'],
                        ]
                    ],
                    $this->config->get('jwt.secret_key'),
                    $this->config->get('jwt.alg')
                )
            ]
        );
        $this->response->setStatusCode(JsonResponse::HTTP_OK);

        return $this->response;
    }

    /**
     * Log the current user out.
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        // Clear JWT.
        return new JsonResponse([]);
    }
}
