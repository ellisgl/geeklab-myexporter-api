<?php

declare(strict_types=1);

namespace App\Core\Http\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP Error 403
 */
class HttpForbiddenException extends HttpException
{
    /**
     * @param                  $message
     * @param Exception | null $previous
     * @param                  $code
     */
    public function __construct($message = '', ?Exception $previous = null, $code = Response::HTTP_FORBIDDEN)
    {
        parent::__construct($message, $code, $previous);
    }
}
