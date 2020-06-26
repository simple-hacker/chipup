<?php

namespace App\Currency;

use Illuminate\Database\Eloquent\Model;

class ExchangeRates extends Model
{
    protected $guarded = [];
    
    protected $dates = [
        'date'
    ];

    protected $casts = [
        'rates' => 'array'
    ];
}
