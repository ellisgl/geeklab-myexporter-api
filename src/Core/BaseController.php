<?php

declare(strict_types=1);

namespace App\Core;

use App\Authentication\AuthenticationService;
use GeekLab\Conf\GLConf;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Core\Request;

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
