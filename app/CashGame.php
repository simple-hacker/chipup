<?php

namespace App;

use App\Abstracts\Game;

class CashGame extends Game
{
    protected $guarded = [];

    protected $with = ['stake', 'variant', 'limit', 'table_size', 'buyIns', 'expenses', 'cashOutModel'];

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

    /**
    * Return the parent's variant model
    * 
    * @return belongsTo
    */
    public function variant()
    {
        return $this->belongsTo('App\Attributes\Variant');
    }

    /**
    * Return the parent's limit model
    * 
    * @return belongsTo
    */
    public function limit()
    {
        return $this->belongsTo('App\Attributes\Limit');
    }

    /**
    * Return the parent's table size model
    * 
    * @return belongsTo
    */
    public function table_size()
    {
        return $this->belongsTo('App\Attributes\TableSize');
    }
}