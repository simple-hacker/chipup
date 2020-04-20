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
}
