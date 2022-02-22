<?php

declare(strict_types=1);

namespace App\Core\Exceptions\Http;

use \Exception;

/**
 * HTTP Error 405
 */
class MethodNotAllowedException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
