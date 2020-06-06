<?php

namespace App\Abstracts;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Exceptions\InvalidDateException;
use App\Exceptions\MultipleCashOutException;
use Illuminate\Database\Eloquent\Model;

abstract class Game extends Model
{
    protected $casts = [
        'user_id' => 'integer',
        'profit' => 'float',
        'prize_pool' => 'integer',
        'position' => 'integer',
        'entries' => 'integer',
    ];

    protected $dates = [
        'start_time', 'end_time'
    ];

    protected $appends = [
        'game_type'
    ];

    /**
    * A Game belongs to a user 
    *
    * @return belongsTo
    */

    public function user()
    {
        return $this->belongsTo('App\User');
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

    /**
    * End the Game by updating the end_time to the current time or given time.
    * 
    * @param Carbon end_time
    * @return mixed
    */
    public function end($end_time = null)
    {
        // Need a Carbon instance of date string that's passed to compare to Carbon start_time
        $dateTest = $end_time ? Carbon::create($end_time) : now();

        // The end_time cannot be before the start_time
        if ($dateTest < $this->start_time) {
            throw new InvalidDateException('Cannot set the end time before the start time', 422);
        }

        return $this->update([
            'end_time' => $end_time
        ]);
    }

    /**
    * Method for calling any adding GameTransaction with a valid string
    * 
    * @param string $transaction_type
    * @param float amount
    * @return mixed
    */
    public function addTransaction(string $transaction_type, float $amount)
    {
        switch($transaction_type) {
            case 'buyIn':
                return $this->addBuyIn($amount);
            case 'expense':
                return $this->addExpense($amount);
            case 'cashOut':
                return $this->addCashOut($amount);
            case 'rebuy':
                return $this->addRebuy($amount);
            case 'addOn':
                return $this->addAddOn($amount);
        }
    }

    /**
    * Add a BuyIn for the game type.
    * This updates the game type's profit by subtracting the BuyIn amount.
    * 
    * @param float amount
    * @return BuyIn
    */
    public function addBuyIn(float $amount)
    {
        return $this->buyIns()->create([
            'amount' => $amount
        ]);
    }

    /**
    * Add an Expense for the game type.
    * This updates the game type's profit by subtracting the Expense amount
    * 
    * @param float amount
    * @return Expense
    */
    public function addExpense(float $amount, string $comments = null)
    {
        return $this->expenses()->create([
            'amount' => $amount,
            'comments' => $comments
        ]);
    }

    /**
    * Add an Expense for the game type.
    * This updates the game type's profit by subtracting the Expense amount
    * 
    * @param float amount
    * @return CashOut
    */
    public function addCashOut(float $amount)
    {
        // if ($this->cashOut) {
        //     throw new MultipleCashOutException();
        // }

        return $this->cashOut()->create([
            'amount' => $amount
        ]);
    }

    /**
    * End the Game and Cash Out
    * One method to simplify Controllers.
    * 
    * @param float amount
    * @return CashOut
    */
    public function endAndCashOut($end_time = null, float $amount = 0)
    {
        $this->end($end_time);
        $this->addCashOut($amount);
    }

    /**
    * Returns the game type's BuyIns
    * 
    * @return morphMany
    */
    public function buyIns()
    {
        return $this->morphMany('App\Transactions\BuyIn', 'game');
    }

    /**
    * Returns the game type's Expenses
    * 
    * @return morphMany
    */
    public function expenses()
    {
        return $this->morphMany('App\Transactions\Expense', 'game');
    }

    /**
    * Returns the game type's CashOut model
    * 
    * @return morphOne
    */
    public function cashOut()
    {
        return $this->morphOne('App\Transactions\CashOut', 'game');
    }

    /**
    * Deletes all the GameType's GameTransactions
    * This is fired in the GameType Model Observer.
    * Have to do it this way because MySQL does not support Laravel polymorphic relationship 
    *
    * By default all Games have buyIns, expenses and a cashOut
    * Tournaments have rebuys and addOns, those are called in the Tournament class.
    *
    * @return void
    */
    public function deleteGameTransactions()
    {
        $this->expenses()->delete();
        $this->cashOut()->delete();
    }

    /**
    * Return the GameType as a string.
    * This is needed when adding transactions to different GameTypes in the GameTransactionController
    *
    * @return string
    */
    public function getGameTypeAttribute()
    {
        return Str::snake(class_basename($this));
    }

    /**
    * Mutate profit in to currency
    *
    * @param Float $profit
    * @return void
    */
    public function getProfitAttribute($profit)
    {
        return $profit / 100;
    }

    /**
    * Mutate profit in to lowest denomination
    *
    * @param Float $profit
    * @return void
    */
    public function setProfitAttribute($profit)
    {
        $this->attributes['profit'] = $profit * 100;
    }

    /**
    * Mutate start_time to be a Carbon instance to UTC
    * So can pass in values like 2020-03-01T16:45:21.000Z
    * and doesn't fail on MySQL timestamp column
    *
    * @param String $start_time
    * @return void
    */
    public function setStartTimeAttribute($start_time)
    {
        if ($start_time) {
            $this->attributes['start_time'] = Carbon::create($start_time);
        } else {
            $this->attributes['start_time'] = now();
        }
    }

    /**
    * Mutate end_time to be a Carbon instance to UTC
    * So can pass in values like 2020-03-01T16:45:21.000Z
    * and doesn't fail on MySQL timestamp column
    *
    * @param String $end_time
    * @return void
    */
    public function setEndTimeAttribute($end_time)
    {
        if ($end_time) {
            $this->attributes['end_time'] = Carbon::create($end_time);
        } else {
            $this->attributes['end_time'] = now();
        }
    }
}
