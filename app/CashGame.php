<?php

namespace App;

use Carbon\Carbon;
use App\Abstracts\Game;

class CashGame extends Game
{
    protected $guarded = [];

    /**
    * Add a BuyIn for the cash game session.
    * This updates the CashGame's profit session by subtracting the BuyIn amount.
    * 
    * @param integer amount
    * @return BuyIn
    */
    public function addBuyIn(int $amount)
    {
        return $this->buyIns()->create([
            'amount' => $amount
        ]);
    }

    /**
    * Add an Expense for the CashGame.
    * This updates the CashGame's profit session by subtracting the Expense amount
    * 
    * @param integer amount
    * @return Expense
    */
    public function addExpense(int $amount)
    {
        return $this->expenses()->create([
            'amount' => $amount
        ]);
    }

    /**
    * Add an Expense for the CashGame.
    * This updates the CashGame's profit session by subtracting the Expense amount
    * 
    * @param integer amount
    * @return CashOut
    */
    public function cashOut(int $amount, Carbon $end_time = null)
    {
        $this->end($end_time);

        return $this->cashOutModel()->create([
            'amount' => $amount
        ]);
    }

    /**
    * Returns the CashGame's BuyIns
    * 
    * @return hasMany
    */
    public function buyIns()
    {
        return $this->hasMany('App\BuyIn');
    }

    /**
    * Returns the CashGame's Expenses
    * 
    * @return hasMany
    */
    public function expenses()
    {
        return $this->hasMany('App\Expense');
    }

    /**
    * Returns the CashGame's CashOut model
    * 
    * @return hasOne
    */
    public function cashOutModel()
    {
        return $this->hasOne('App\CashOut');
    }
}