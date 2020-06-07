<?php

namespace App;

use App\Abstracts\Game;

class CashGame extends Game
{
    protected $guarded = [];

    protected $with = ['stake', 'variant', 'limit', 'table_size', 'buyIns', 'expenses', 'cashOut'];

    protected $cascadeDeletes = ['buyIns', 'expenses', 'cashOut'];

    /**
    * Return the parent's stake model
    * 
    * @return belongsTo
    */
    public function stake()
    {
        return $this->belongsTo('App\Attributes\Stake');
    }
}