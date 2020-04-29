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
    * @param float amount
    * @return BuyIn
    */
    public function addBuyIn(float $amount)
    {
        if ($this->buyIn()->count() > 0) {
            throw new MultipleBuyInsNotAllowed();
        }

        return $this->buyIn()->create([
            'amount' => $amount
        ]);
    }

    /**
    * Add a Rebuy for the tournament.
    * This updates the Tournament's profit by subtracting the Rebuy amount.
    *
    * @param float amount
    * @return Rebuy
    */
    public function addRebuy(float $amount)
    {
        return $this->rebuys()->create([
            'amount' => $amount
        ]);
    }

    /**
    * Add a AddOn for the tournament.
    * This updates the Tournament's profit by subtracting the Rebuy amount.
    *
    * @param float amount
    * @return AddOn
    */
    public function addAddOn(float $amount)
    {
        return $this->addOns()->create([
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
        return $this->morphOne('App\Transactions\BuyIn', 'game');
    }

    /**
    * Returns the Tournament's Rebuys
    * 
    * @return morphMany
    */
    public function rebuys()
    {
        return $this->morphMany('App\Transactions\Rebuy', 'game');
    }

    /**
    * Returns the Tournament's Rebuys
    * 
    * @return morphMany
    */
    public function addOns()
    {
        return $this->morphMany('App\Transactions\AddOn', 'game');
    }


    /**
    * Extends parent's deleteGameTransactions to include Tournament relationships.
    * 
    * @return void
    */
    public function deleteGameTransactions()
    {
        $this->buyIn()->delete();
        $this->rebuys()->delete();
        $this->addOns()->delete();

        parent::deleteGameTransactions();
    }
}
