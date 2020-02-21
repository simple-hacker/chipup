<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BuyIn extends Model
{
    protected $guarded = [];

    /**
    * Returns the BuyIn's CashGame 
    *
    * @return belongsTo
    */
    public function cashGame()
    {
        return $this->belongsTo('App\CashGame');
    }
}
