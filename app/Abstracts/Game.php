<?php

namespace App\Abstracts;

use Illuminate\Support\Carbon;
use App\Exceptions\InvalidDate;
use Illuminate\Database\Eloquent\Model;
use ShiftOneLabs\LaravelCascadeDeletes\CascadesDeletes;

abstract class Game extends Model
{
    protected $casts = [
        'profit' => 'integer'
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
    * End the Game by updating the end_time to the current time or given time.
    * 
    * @param Carbon end_time
    * @return mixed
    */
    public function end(Carbon $end_time = null)
    {
        $end_time  = $end_time ?? now();

        // The end_time cannot be before the start_time
        if ($end_time < $this->start_time) {
            throw new InvalidDate('Cannot set the end time before the start time');
        }

        return $this->update([
            'end_time' => $end_time
        ]);
    }

    /**
    * Add a BuyIn for the game type.
    * This updates the game type's profit by subtracting the BuyIn amount.
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
    * Add an Expense for the game type.
    * This updates the game type's profit by subtracting the Expense amount
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
    * Add an Expense for the game type.
    * This updates the game type's profit by subtracting the Expense amount
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
    public function cashOutModel()
    {
        return $this->morphOne('App\Transactions\CashOut', 'game');
    }
}
