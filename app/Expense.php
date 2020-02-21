<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $guarded = [];

    /**
    * Returns the Expense's CashGame 
    *
    * @return belongsTo
    */
    public function cashGame()
    {
        return $this->belongsTo('App\CashGame');
    }
}
