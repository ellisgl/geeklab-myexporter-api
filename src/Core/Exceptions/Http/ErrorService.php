<?php

declare(strict_types=1);

namespace App\Core\Exceptions\Http;

use App\Authentication\AuthorizationException;
use App\Core\BaseController;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * This is for handling HTTP errors.
 */
class ErrorService
{
    // @Todo: Methods for logging?
    // @Todo: More HTTP error code handling.
    /**
     * Return an HTTP 401 UNAUTHORIZED response.
     *
     * @param string | null $message
     *
     * @return JsonResponse
     */
    public function error401(?string $message = null): JsonResponse
    {
        $response = new JsonResponse();
        $response->setStatusCode(JsonResponse::HTTP_UNAUTHORIZED);
        $response->setContent($message ?: '401 - Unauthorized');

        return $response;
    }

    /**
     * Create an HTTP 404 NOT FOUND response.
     *
     * @param string | null $message
     *
     * @return JsonResponse
     */
    public function error404(?string $message = null): JsonResponse
    {
        $response = new JsonResponse();
        $response->setStatusCode(JsonResponse::HTTP_NOT_FOUND);
        $response->setContent($message ?: '404 - Page not found');

        return $response;
    }

    /**
     * Create an HTTP 405 METHOD NOT ALLOWED response.
     *
     * @param string | null $message
     *
     * @return JsonResponse
     */
    public function error405(?string $message = null): JsonResponse
    {
        $response = new JsonResponse();
        $response->setStatusCode(JsonResponse::HTTP_METHOD_NOT_ALLOWED);
        $response->setContent($message ?: '405 - Method not allowed');

        return $response;
    }

    public function error500(?string $message = null): JsonResponse
    {
        $response = new JsonResponse();
        $response->setStatusCode(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        $response->setContent($message ?: '500 - Internal server error');


        return $response;
    }
    /**
     * Convert an exception into a matching error response.
     *
     * @param Exception $e
     *
     * @return JsonResponse
     */
    public function handleError(Exception $e): JsonResponse
    {
        // Detect HTTP error exceptions and return the correct response.
        // Will need to use better class names and more classes.
        switch (get_class($e)) {
            case AuthorizationException::class:
                return $this->error401($e->getMessage() ?: null);
            case NotFoundException::class:
                return $this->error404($e->getMessage() ?: null);
            case MethodNotAllowedException::class:
                return $this->error405($e->getMessage() ?: null);
            default:
                // I don't think we want the exception message to get out to the wild, do we?
                return $this->error500();
        }
    }
}
