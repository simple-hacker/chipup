<?php

namespace App\Transactions;

use Illuminate\Database\Eloquent\Model;

class Bankroll extends Model
{
    protected $guarded = [];

    protected $table = 'bankroll_transactions';

    protected $casts = [
        'user_id' => 'integer',
        'amount' => 'integer',
    ];

    protected $dates = [
        'date'
    ];

    protected $with = [];

    /**
    * Returns the user the Bankroll belongs to
    *
    * @return belongsTo
    */
    public function user()
    {
        return $this->belongsTo('App\User');
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
