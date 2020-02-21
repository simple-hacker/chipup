<?php

namespace App\Transactions;

use App\Abstracts\NegativeGameTransaction;

class BuyIn extends NegativeGameTransaction
{
    protected $guarded = [];
}
