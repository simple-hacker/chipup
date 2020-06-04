<?php

namespace App\Exceptions;

use Exception;

class InvalidGameTypeException extends Exception
{
    protected $message = 'Invalid Game Type';

    protected $code = 422;
}
