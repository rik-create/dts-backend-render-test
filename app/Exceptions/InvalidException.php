<?php

namespace App\Exceptions;

use Exception;

class InvalidException extends Exception
{
    public function __construct($message = "Invalid", $code = 400, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
