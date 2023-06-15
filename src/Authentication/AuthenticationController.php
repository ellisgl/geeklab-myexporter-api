<?php

declare(strict_types=1);

namespace App\Authentication;

use App\Core\BaseController;
use App\Core\Http\Exceptions\HttpMethodNotAllowedException;
use App\Core\Http\Exceptions\HttpUnauthorizedException;
use App\Core\Http\Request;
use JsonException;
use OpenApi\Attributes as OAT;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthenticationController extends BaseController
{
    /**
     * @return JsonResponse
     * @throws HttpMethodNotAllowedException
     * @throws HttpUnauthorizedException
     * @throws JsonException
     */
    #[OAT\Post(
        path       : '/login',
        operationId: 'authLogin',
        summary    : 'Login with server id, username and password',
        requestBody: new OAT\RequestBody(
            content: new OAT\JsonContent(
                properties: [
                    new OAT\Property(property: 'server_id', type: 'number'),
                    new OAT\Property(property: 'username', type: 'string'),
                    new OAT\Property(property: 'password', type: 'string'),
                ]
            )
        ),
        tags       : ['auth']
    )]
    #[OAT\Response(
        response   : 200,
        description: 'JWT Response',
        content    : new OAT\JsonContent(
            properties: [
                new OAT\Property(property: 'message', type: 'string', example: 'success'),
                new OAT\Property(
                    property: 'jwt',
                    type    : 'string',
                    example : 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAw'
                ),
            ]
        )
    )]
    #[OAT\Response(
        response   : 401,
        description: 'Unauthorized'
    )]
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
