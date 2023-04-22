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
    public function __construct(
        protected readonly GLConf $config,
        protected readonly Request $request,
        protected readonly JsonResponse $response,
        protected readonly AuthenticationService $authenticationService
    ) {
    }
}
