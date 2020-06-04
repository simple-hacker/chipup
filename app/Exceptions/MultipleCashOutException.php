<?php

namespace App\Exceptions;

use Exception;

class MultipleCashOutException extends Exception
{
    protected $message = 'Game can only have one cash out.';

    protected $code = 422;
}
