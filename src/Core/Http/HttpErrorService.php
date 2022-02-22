<?php

declare(strict_types=1);

namespace App\Core\Http;

use App\Core\Http\Exceptions\HttpForbiddenException;
use App\Core\Http\Exceptions\HttpUnauthorizedException;
use App\Core\Http\Exceptions\HttpBadRequestException;
use App\Core\Http\Exceptions\HttpMethodNotAllowedException;
use App\Core\Http\Exceptions\HttpNotFoundException;
use \Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * This is for handling HTTP errors.
 */
class HttpErrorService
{
    // @Todo: Methods for logging?
    // @Todo: More HTTP error code handling.
    /**
     * Return an HTTP 400 BAD REQUEST response
     *
     * @param string | null $message
     *
     * @return JsonResponse
     */
    public function error400(?string $message = null): JsonResponse
    {
        $response = new JsonResponse();
        $response->setStatusCode(JsonResponse::HTTP_BAD_REQUEST);
        $response->setContent($message ?: '400 - Bad request');

        return $response;
    }

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
     * Return an HTTP 403 FORBIDDEN response.
     *
     * @param string | null $message
     *
     * @return JsonResponse
     */
    public function error403(?string $message = null): JsonResponse
    {
        $response = new JsonResponse();
        $response->setStatusCode(JsonResponse::HTTP_FORBIDDEN);
        $response->setContent($message ?: '403 - Forbidden');

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
            case HttpBadRequestException::class:
                return $this->error400($e->getMessage() ?: null);
            case HttpUnauthorizedException::class:
                return $this->error401($e->getMessage() ?: null);
            case HttpForbiddenException::class:
                return $this->error403($e->getMessage() ?: null);
            case HttpNotFoundException::class:
                return $this->error404($e->getMessage() ?: null);
            case HttpMethodNotAllowedException::class:
                return $this->error405($e->getMessage() ?: null);
            default:
                // I don't think we want the exception message to get out to the wild, do we?
                return $this->error500();
        }
    }
}
