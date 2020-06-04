<?php

namespace App\Exceptions;

use Exception;

class ClashingSessionsException extends Exception
{
    protected $code = 422;
}
