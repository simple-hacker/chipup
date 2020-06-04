<?php

namespace App\Exceptions;

use Exception;

class SessionNotStartedException extends Exception
{
    protected $code = 422;
}
