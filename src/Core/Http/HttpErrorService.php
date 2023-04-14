<?php

declare(strict_types=1);

namespace App\Core\Http;

use App\Core\Http\Exceptions\HttpBadRequestException;
use App\Core\Http\Exceptions\HttpForbiddenException;
use App\Core\Http\Exceptions\HttpMethodNotAllowedException;
use App\Core\Http\Exceptions\HttpNotFoundException;
use App\Core\Http\Exceptions\HttpUnauthorizedException;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * This is for handling HTTP errors.
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
     * @param Request         $request
     * @param Exception       $e
     * @param Response | null $response
     *
     * @return Response
     */
    public function handleError(Request $request, Exception $e, ?Response $response = null): Response
    {
        if (!$response) {
            $response = new Response();
        }

        // Detect HTTP error exceptions and return the correct response.
        // Will need to use better class names and more classes.
        if (
            in_array(
                get_class($e),
                [
                    HttpBadRequestException::class,
                    HttpUnauthorizedException::class,
                    HttpForbiddenException::class,
                    HttpNotFoundException::class,
                    HttpMethodNotAllowedException::class,
                ],
            )
        ) {
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

        if ($this->logger && in_array($response->getStatusCode(), $this->logErrorCodes)) {
            $this->logError($response->getStatusCode(), $request, $response, $e->getMessage());
        }

        return $response;
    }

    /**
     * Log HTTP error.
     *
     * @param int             $httpStatusCode
     * @param Request         $request
     * @param Response | null $response
     * @param string | null   $message
     *
     * @return void
     */
    private function logError(
        int $httpStatusCode,
        Request $request,
        ?Response $response = null,
        ?string $message = null,
    ): void {
        $this->logger->error(
            "$httpStatusCode - " . Response::$statusTexts[$httpStatusCode],
            [$message, $request, $response],
        );
    }
}
