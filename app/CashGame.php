<?php

namespace App;

use App\Abstracts\Game;

class CashGame extends Game
{
    protected $guarded = [];

    protected $with = ['stake', 'variant', 'limit', 'table_size', 'buyIns', 'expenses', 'cashOut'];

    /**
    * Extends parent's deleteGameTransactions to include Tournament relationships.
    * 
    * @return void
    */
    public function deleteGameTransactions()
    {
        $this->buyIns()->delete();

        parent::deleteGameTransactions();
    }

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