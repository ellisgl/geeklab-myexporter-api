<?php

declare(strict_types=1);

namespace App\Core\Http\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP Error 401
 */
class HttpUnauthorizedException extends HttpException
{
    /**
     * @param                  $message
     * @param Exception | null $previous
     * @param                  $code
     */
    public function __construct($message = '', ?Exception $previous = null, $code = Response::HTTP_UNAUTHORIZED)
    {
        parent::__construct($message, $code, $previous);
    }
}
