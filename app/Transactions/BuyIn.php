<?php

namespace App\Transactions;

use Illuminate\Database\Eloquent\Model;

class BuyIn extends Model
{
    protected $guarded = [];

    /**
    * Returns the BuyIn's game type model. 
    *
    * @return morphTo
    */
    public function game()
    {
        return $this->morphTo();
    }
}
