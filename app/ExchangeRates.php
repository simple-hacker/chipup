<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExchangeRates extends Model
{
    protected $dates = [
        'date'
    ];

    protected $casts = [
        'rates' => 'array'
    ];
}
