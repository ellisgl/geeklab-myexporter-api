<?php

declare(strict_types=1);

namespace App\Core\Controllers;

use App\Core\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * This is for handling HTTP errors.
 */
class ErrorController extends BaseController
{
    // @Todo: More HTTP error code handling.
    public function error401(): JsonResponse
    {
        $this->response->setStatusCode(JsonResponse::HTTP_UNAUTHORIZED);
        $this->response->setContent('401 - Unauthorized');

        return $this->response;
    }

    /**
     * Create an HTTP 404 NOT FOUND error page.
     * @return JsonResponse
     */
    public function error404(): JsonResponse
    {
        $this->response->setStatusCode(JsonResponse::HTTP_NOT_FOUND);
        $this->response->setContent('404 - Page not found');

        return $this->response;
    }

    /**
     * Create an HTTP 405 Method NOT ALLOWED error page.
     * @return JsonResponse
     */
    public function error405(): JsonResponse
    {
        $this->response->setStatusCode(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
        $this->response->setContent('405 - Method not allowed');

        return $this->response;
    }
}
