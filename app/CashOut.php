<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CashOut extends Model
{
    protected $guarded = [];

    /**
    * Returns the CashOut's game type model
    * 
    * @return morphTo
    */
    public function game()
    {
        return $this->morphTo();
    }
}
