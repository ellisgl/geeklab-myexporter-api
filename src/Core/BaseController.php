<?php

declare(strict_types=1);

namespace App\Core;

use App\Authentication\AuthenticationService;
use App\Core\Http\Request;
use GeekLab\Conf\GLConf;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @OA\Info(title="GeekLab MyExporter API", version="0.1")
 * @OA\SecurityScheme(
 *      securityScheme="bearerAuth",
 *      in="header",
 *      name="bearerAuth",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="JWT",
 * )
 */
class BaseController
{
    protected GLConf       $config;
    protected Request      $request;
    protected JsonResponse $response;
    protected AuthenticationService $authenticationService;

    public function __construct(
        GLConf $config,
        Request $request,
        JsonResponse $response,
        AuthenticationService $authenticationService
    ) {
        $this->config = $config;
        $this->request = $request;
        $this->response = $response;
        $this->authenticationService = $authenticationService;
    }
}
