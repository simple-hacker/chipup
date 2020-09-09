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

    protected $appends = ['full_stake'];

    /**
    * Get the stake full text using small and big blind
    * e.g. 1/2
    * 
    * @return string
    */
    public function getFullStakeAttribute() : String
    {
        return $this->small_blind . '/' . $this->big_blind;
    }
}
