<?php

namespace App\Exceptions;

use Exception;

class SessionInProgressException extends Exception
{
    protected $code = 422;
}
