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
     * @OA\Post(
     *     path="/login",
     *     summary="Login with server id, username and password",
     *     operationId="authLogin",
     *     tags={"auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"server_id", "username", "password"},
     *             @OA\Property(property="server_id", type="number"),
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="password", type="string"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="JWT Response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="jwt", type="string"),
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     * @return JsonResponse
     * @throws JsonException
     * @throws HttpMethodNotAllowedException
     * @throws HttpUnauthorizedException
     */
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
