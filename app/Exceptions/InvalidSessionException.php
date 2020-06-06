<?php

namespace App\Exceptions;

use Exception;

class InvalidSessionException extends Exception
{
    protected $message = 'Invalid Live Session';

    protected $code = 422;
}
