<?php

declare(strict_types=1);

namespace App\Authentication;

use App\Core\BaseController;
use \JsonException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use App\Core\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthenticationController extends BaseController
{
    /**
     * Perform login.
     *
     * @return JsonResponse
     * @throws JsonException
     */
    public function login(): JsonResponse
    {
        if (Request::METHOD_POST !== $this->request->getMethod()) {
            throw new BadRequestException();
        }

        $this->response->setData(
            [
                'message' => 'Successful',
                'jwt' => $this->authenticationService->doAuthentication($this->request)
            ]
        );
        $this->response->setStatusCode(JsonResponse::HTTP_OK);

        return $this->response;
    }
}
