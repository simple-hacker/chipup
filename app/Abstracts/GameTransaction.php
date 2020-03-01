<?php

namespace App\Abstracts;

use Illuminate\Database\Eloquent\Model;

abstract class GameTransaction extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount' => 'integer'
    ];
    
    /**
    * Returns the Transaction's game type model
    * 
    * @return morphTo
    */
    public function game()
    {
        return $this->morphTo();
    }

    /**
    * Return the User from belongsToThrough
    * 
    * @param 
    * @return morphTo
    */
    public function user()
    {
        return $this->game->user;
    }
}
