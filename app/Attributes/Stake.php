<?php

namespace App\Attributes;

use Illuminate\Database\Eloquent\Model;

class Stake extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'user_id' => 'integer',
        'small_blind' => 'float',
        'big_blind' => 'float',
        'straddle_1' => 'float',
        'straddle_2' => 'float',
        'straddle_3' => 'float',
    ];
}
