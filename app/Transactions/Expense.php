<?php

namespace App\Transactions;

use App\Abstracts\NegativeGameTransaction;

class Expense extends NegativeGameTransaction
{
    protected $guarded = [];
}
