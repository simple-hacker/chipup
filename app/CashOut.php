<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CashOut extends Model
{
    protected $guarded = [];

    /**
    * Returns the CashOut's CashGame
    * 
    * @return belongsTo
    */
    public function cashGame()
    {
        return $this->belongsTo('App\CashGame');
    }
}
