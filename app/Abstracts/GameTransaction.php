<?php

namespace App\Abstracts;

use Illuminate\Database\Eloquent\Model;

abstract class GameTransaction extends Model
{
    protected $guarded = [];
    
    /**
    * Returns the Transaction's game type model
    * 
    * @return morphTo
    */
    public function game()
    {
        return $this->morphTo();
    }
}
