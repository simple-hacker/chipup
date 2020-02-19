<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CashGame extends Model
{
    protected $guarded = [];


    /**
    * A session belongs to a user 
    *
    * @return belongsTo
    */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
