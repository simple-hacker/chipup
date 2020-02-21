<?php

namespace App;

use App\Abstracts\Game;
use App\Exceptions\MultipleBuyInsNotAllowed;

class Tournament extends Game
{
    protected $guarded = [];

    /**
    * Add a BuyIn for the tournament.
    * This updates the Tournament's profit by subtracting the BuyIn amount.
    * This overwrites the abstract Game->addBuyIn() function because a tournament should only have
    * one buyIn, where as Cash can have multiple
    * 
    * @param integer amount
    * @return BuyIn
    */
    public function addBuyIn(int $amount)
    {
        if ($this->buyIn()->count() > 0) {
            throw new MultipleBuyInsNotAllowed();
        }

        return $this->buyIn()->create([
            'amount' => $amount
        ]);
    }

    /**
    * Returns the Tournament's BuyIn
    * 
    * @return morphMany
    */
    public function buyIn()
    {
        return $this->morphOne('App\BuyIn', 'game');
    }
}
