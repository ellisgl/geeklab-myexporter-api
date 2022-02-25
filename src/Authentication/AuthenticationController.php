<?php

declare(strict_types=1);

namespace App\Authentication;

use App\Core\BaseController;
use App\Core\Http\Exceptions\HttpUnauthorizedException;
use App\Core\Request;
use \JsonException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthenticationController extends BaseController
{
    /**
     * Perform login.
     *
     * @OA\Post(
     *     path="/login",
     *     summary="Login with server index, username and password",
     *     operationId="authLogin",
     *     tags={"auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"host", "username", "password"},
     *             @OA\Property(property="host", type="integer"),
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
     * @throws HttpUnauthorizedException
     */
    public function login(): JsonResponse
    {
        if (Request::METHOD_POST !== $this->request->getMethod()) {
            throw new BadRequestException();
        }

        $this->response->setData(
            [
                'message' => 'success',
                'jwt'     => $this->authenticationService->doAuthentication($this->request)
            ]
        );
        $this->response->setStatusCode(JsonResponse::HTTP_OK);

        return $this->response;
    }
}
