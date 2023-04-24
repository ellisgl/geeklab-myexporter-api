<?php

declare(strict_types=1);

namespace App\Authentication;

use App\Core\BaseController;
use App\Core\Http\Exceptions\HttpMethodNotAllowedException;
use App\Core\Http\Exceptions\HttpUnauthorizedException;
use App\Core\Http\Request;
use JsonException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthenticationController extends BaseController
{
    /**
     * Perform login.
     *
     * @return JsonResponse
     * @throws JsonException
     * @throws HttpMethodNotAllowedException
     * @throws HttpUnauthorizedException
     */
    #[OA\Post(path: '/login', operationId: 'authLogin', summary: 'Login with server id, username and password', tags: ['auth'])]
    #[OA\RequestBody(
        new OA\JsonContent(
            required  : ['server_id', 'username', 'password'],
            properties: [
                new OA\Property('server_id', new OA\Schema(type: 'number')),
                new OA\Property('username', new OA\Schema(type: 'string')),
                new OA\Property('password', new OA\Schema(type: 'string')),
            ]
        )
    )]
    #[OA\Response(
        response   : 200,
        description: 'JWT Response',
        content    : new OA\MediaType(
            mediaType: 'application/json',
            schema   : new OA\Schema(
                allOf: [
                    new OA\Schema(
                        properties: [
                            new OA\Property('message', new OA\Schema(type: 'string')),
                            new OA\Property('jwt', new OA\Schema(type: 'string')),
                        ]
                    ),
                ]
            ),
        )
    )]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function login(): JsonResponse
    {
        if (Request::METHOD_POST !== $this->request->getMethod()) {
            throw new HttpMethodNotAllowedException('Does not support ' . $this->request->getMethod() . ' method');
        }

        $this->response->setData(
            [
                'message' => 'success',
                'jwt' => $this->authenticationService->doAuthentication($this->request),
            ],
        );
        $this->response->setStatusCode(JsonResponse::HTTP_OK);

        return $this->response;
    }
}
