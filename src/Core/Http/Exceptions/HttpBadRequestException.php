<?php

declare(strict_types=1);

namespace App\Core\Http\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * HTTP Error 400
 */
class HttpBadRequestException extends HttpException
{
    /**
     * @param                  $message
     * @param Exception | null $previous
     * @param                  $code
     */
    public function __construct($message = '', ?Throwable $previous = null, $code = Response::HTTP_BAD_REQUEST)
    {
        parent::__construct($message, $code, $previous);
    }
}
