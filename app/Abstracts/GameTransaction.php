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

    /**
    * Mutate amount in to currency
    *
    * @param  Integer $amount
    * @return void
    */
    public function getAmountAttribute($amount)
    {
        return $amount / 100;
    }

    /**
    * Mutate amount in to lowest denomination
    *
    * @param  Float $amount
    * @return void
    */
    public function setAmountAttribute($amount)
    {
        $this->attributes['amount'] = $amount * 100;
    }
}
