<?php

namespace App\Abstracts;

use App\CurrencyConverter;
use Illuminate\Database\Eloquent\Model;

abstract class GameTransaction extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount' => 'float'
    ];
    
    /**
    * Returns the Transaction's game type model
    * 
    * @return morphTo
    */
    public function game()
    {
        return $this->morphTo();
    }

    /**
    * Return the User from belongsToThrough
    * 
    * @param 
    * @return morphTo
    */
    public function user()
    {
        return $this->game->user;
    }

    /**
    * Mutate amount in to currency
    *
    * @param Float $amount
    * @return void
    */
    public function getAmountAttribute($amount)
    {
        return $amount / 100;
    }

    /**
    * Mutate amount in to lowest denomination
    *
    * @param Float $amount
    * @return void
    */
    public function setAmountAttribute($amount)
    {
        $this->attributes['amount'] = $amount * 100;
    }

    /**
    * Mutate amount in to session currency
    *
    * @param Float $amount
    * @return void
    */
    public function getSessionLocaleAmountAttribute()
    {
        if ($this->currency === $this->game->currency) {
            return $this->amount;
        }

        $currencyConverter = new CurrencyConverter();

        return $currencyConverter
                ->convertFrom($this->currency)
                ->convertTo($this->game->currency)
                ->convertAt($this->game->start_time)
                ->convert($this->amount);
    }

    /**
    * Mutate amount in to user currency
    *
    * @param Float $amount
    * @return void
    */
    public function getLocaleAmountAttribute()
    {
        if ($this->currency === $this->game->user->currency) {
            return $this->amount;
        }

        $currencyConverter = new CurrencyConverter();

        return $currencyConverter
                ->convertFrom($this->currency)
                ->convertTo($this->game->user->currency)
                ->convertAt($this->game->start_time)
                ->convert($this->amount);
    }
}
