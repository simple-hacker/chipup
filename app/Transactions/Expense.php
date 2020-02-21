<?php

namespace App\Transactions;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $guarded = [];

    /**
    * Returns the Expense's game type model. 
    *
    * @return morphTo
    */
    public function game()
    {
        return $this->morphTo();
    }
}
