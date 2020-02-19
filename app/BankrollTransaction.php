<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BankrollTransaction extends Model
{
    protected $guarded = [];

    /**
    * Returns the user the BankrollTransaction belongs to
    *
    * @return belongsTo
    */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
