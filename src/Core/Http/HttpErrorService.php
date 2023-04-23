<?php

declare(strict_types=1);

namespace App\Core\Http;

use App\Core\Http\Exceptions\HttpException;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Create a Response object from exceptions.
 * This class will also log the error, if configured to do so.
 */
class HttpErrorService
{
    public function __construct(
        private readonly ?LoggerInterface $logger = null,
        private readonly array $logErrorCodes = [400, 401, 403, 404, 405, 500],
    ) {
    }

    /**
     * Convert an exception into a matching error response.
     *
     * @param Request                        $request
     * @param Exception                      $e
     * @param JsonResponse | Response | null $response
     *
     * @return Response
     */
    public function handleError(
        Request $request,
        Exception $e,
        JsonResponse | Response | null $response = null,
    ): Response {
        if (!$response) {
            $response = new Response();
        }

        // Detect HTTP error exceptions and return the correct response.
        // Will need to use better class names and more classes.
        if ($e instanceof HttpException) {
            $response->setStatusCode($e->getCode());
            $response->setContent(
                $e->getMessage() ?: $e->getCode() . ' - ' . Response::$statusTexts[$e->getCode()],
            );
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $response->setContent(
                $e->getMessage()
                    ?: Response::HTTP_INTERNAL_SERVER_ERROR .
                    ' - ' .
                    Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR],
            );
        }

        if (in_array($response->getStatusCode(), $this->logErrorCodes)) {
            $this->logError($e, $response->getStatusCode(), $request, $response);
        }

        return $response;
    }

    /**
     * Log HTTP error.
     *
     * @param Exception       $e
     * @param int             $httpStatusCode
     * @param Request         $request
     * @param Response | null $response
     *
     * @return void
     */
    private function logError(
        Exception $e,
        int $httpStatusCode,
        Request $request,
        ?Response $response = null,
    ): void {
        if ($this->logger) {
            $this->logger->error(
                "$httpStatusCode - " . Response::$statusTexts[$httpStatusCode],
                [$e, $request, $response],
            );
        } else {
            $r = [
                'method' => $request->getMethod(),
                'body' =>
                in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT, Request::METHOD_PATCH])
                    ? $request->toArray()
                    : [],
                'uri' => $request->getUri(),
            ];
            error_log(
                json_encode(
                    [
                        'exception' => $e->getMessage(),
                        'request' => $r,
                        'trace' => $e?->getTraceAsString(),
                        'response_status_code' => $response->getStatusCode(),
                        'response_content' => $response->getContent(),
                    ],
                ),
            );
        }
    }
}
