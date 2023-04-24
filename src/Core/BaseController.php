<?php

declare(strict_types=1);

namespace App\Core;

use App\Authentication\AuthenticationService;
use App\Core\Http\Request;
use GeekLab\Conf\GLConf;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;

#[OA\Info(version: '0.1', title: 'GeekLab MyExporter API')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type          : 'http',
    name          : 'bearerAuth',
    in            : 'header',
    bearerFormat  : 'JWT',
    scheme        : 'bearer'
)]
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
