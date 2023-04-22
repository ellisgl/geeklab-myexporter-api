<?php

declare(strict_types=1);

namespace App\Core\Http\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP Error 405
 */
class HttpMethodNotAllowedException extends HttpException
{
    /**
     * @param                  $message
     * @param Exception | null $previous
     * @param                  $code
     */
    public function __construct($message = '', ?Exception $previous = null, $code = Response::HTTP_METHOD_NOT_ALLOWED)
    {
        parent::__construct($message, $code, $previous);
    }
}
