<?php

declare(strict_types=1);

namespace App\Core;

use GeekLab\Conf\GLConf;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class BaseController
{
    protected GLConf       $config;
    protected Request      $request;
    protected JsonResponse $response;

    public function __construct(
        GLConf $config,
        Request $request,
        JsonResponse $response
    ) {
        $this->config = $config;
        $this->request = $request;
        $this->response = $response;
    }
}
