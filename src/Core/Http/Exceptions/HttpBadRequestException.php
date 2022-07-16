<?php

declare(strict_types=1);

namespace App\Core\Http\Exceptions;

use \Exception;

/**
 * HTTP Error 400
 */
class HttpBadRequestException extends Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
