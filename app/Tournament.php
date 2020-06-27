<?php

namespace App;

use App\Abstracts\Game;
use App\Exceptions\MultipleBuyInsNotAllowedException;

class Tournament extends Game
{
    protected $guarded = [];

    protected $with = ['variant', 'limit', 'buyIn', 'expenses', 'rebuys', 'addOns', 'cashOut'];

    protected $cascadeDeletes = ['buyIn', 'expenses', 'cashOut', 'rebuys', 'addOns'];

    /**
    * Add a BuyIn for the tournament.
    * This updates the Tournament's profit by subtracting the BuyIn amount.
    * This overwrites the abstract Game->addBuyIn() function because a tournament should only have
    * one buyIn, where as Cash can have multiple
    * 
    * @param float amount
    * @return BuyIn
    */
    public function addBuyIn(float $amount, string $currency = null)
    {
        if ($this->buyIn()->count() > 0) {
            throw new MultipleBuyInsNotAllowedException();
        }

        return $this->buyIn()->create([
            'amount' => $amount,
            'currency' => $currency ?? $this->currency ?? auth()->user()->currency ?? 'GBP',
        ]);
    }

    /**
    * Add a Rebuy for the tournament.
    * This updates the Tournament's profit by subtracting the Rebuy amount.
    *
    * @param float amount
    * @return Rebuy
    */
    public function addRebuy(float $amount, string $currency = null)
    {
        return $this->rebuys()->create([
            'amount' => $amount,
            'currency' => $currency ?? $this->currency ?? auth()->user()->currency ?? 'GBP',
        ]);
    }

    /**
    * Add a AddOn for the tournament.
    * This updates the Tournament's profit by subtracting the Rebuy amount.
    *
    * @param float amount
    * @return AddOn
    */
    public function addAddOn(float $amount, string $currency = null)
    {
        return $this->addOns()->create([
            'amount' => $amount,
            'currency' => $currency ?? $this->currency ?? auth()->user()->currency ?? 'GBP',
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
    * Mutate profit
    *
    * @return Integer
    */
    public function getProfitAttribute()
    {
        return 0 -$this->buyInAmount() - $this->totalRebuysAmount() - $this->totalAddOnsAmount() - $this->totalExpensesAmount() + $this->cashOutAmount();
    }

    /**
    * Return cash out amount in session currency
    * 
    * @return Integer
    */
    public function buyInAmount()
    {
        return $this->buyIn->session_locale_amount ?? 0;
    }

    /**
    * Return total rebuys amount converted in to session currency
    * 
    * @return Integer
    */
    public function totalRebuysAmount()
    {
        $total = $this->rebuys->reduce(function ($total, $rebuy) {
            return $total + $rebuy->session_locale_amount;
        }, 0);

        return $total;
    }

    /**
    * Return total rebuys amount converted in to session currency
    * 
    * @return Integer
    */
    public function totalAddOnsAmount()
    {
        $total = $this->addOns->reduce(function ($total, $addOn) {
            return $total + $addOn->session_locale_amount;
        }, 0);

        return $total;
    }

    /**
    * Mutate prize_pool in to currency
    *
    * @param Float $prize_pool
    * @return void
    */
    public function getPrizePoolAttribute($prize_pool)
    {
        return $prize_pool / 100;
    }

    /**
    * Mutate prize_pool in to lowest denomination
    *
    * @param Float $prize_pool
    * @return void
    */
    public function setPrizePoolAttribute($prize_pool)
    {
        $this->attributes['prize_pool'] = $prize_pool * 100;
    }
}
