<?php

declare(strict_types=1);

namespace App\Core\Http\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP Error 404
 */
class HttpNotFoundException extends Exception
{
    /**
     * @param                  $message
     * @param Exception | null $previous
     * @param                  $code
     */
    public function __construct($message = '', Exception $previous = null, $code = Response::HTTP_NOT_FOUND)
    {
        parent::__construct($message, $code, $previous);
    }
}
