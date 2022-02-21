<?php

declare(strict_types=1);

namespace App\Core\Controllers;

use App\Authentication\NotLoggedInException;
use App\Core\BaseController;
use \Exception;
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

    public function handleError(Exception $e): JsonResponse
    {
        // Detect HTTP error exceptions and return the correct response.
        // Will need to use better class names and more classes.
        switch (get_class($e)) {
            case NotLoggedInException::class:
                return $this->error401();
            default:
                $this->response->setStatusCode(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
                $this->response->setContent('500 - Internal server error');

                return $this->response;
        }
    }
}
