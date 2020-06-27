<?php

namespace App\Abstracts;

use App\Currency\CurrencyConverter;
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
    * @param Integer $amount
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
        // Round up to 2dp incase a value like 33.33333 is submitted.
        $amount = round($amount, 2, PHP_ROUND_HALF_UP);

        $this->attributes['amount'] = $amount * 100;
    }

    /**
    * Mutate locale_amount in to currency
    *
    * @param Integer $locale_amount
    * @return void
    */
    public function getLocaleAmountAttribute($locale_amount)
    {
        return $locale_amount / 100;
    }

    /**
    * Mutate locale_amount in to lowest denomination
    *
    * @param Float $locale_amount
    * @return void
    */
    public function setLocaleAmountAttribute($locale_amount)
    {
        $this->attributes['locale_amount'] = $locale_amount * 100;
    }

    /**
    * Mutate session_locale_amount in to currency
    *
    * @param Integer $session_locale_amount
    * @return void
    */
    public function getSessionLocaleAmountAttribute($session_locale_amount)
    {
        return $session_locale_amount / 100;
    }

    /**
    * Mutate session_locale_amount in to lowest denomination
    *
    * @param Float $session_locale_amount
    * @return void
    */
    public function setSessionLocaleAmountAttribute($session_locale_amount)
    {
        $this->attributes['session_locale_amount'] = $session_locale_amount * 100;
    }

    // /**
    // * Mutate amount in to session currency
    // *
    // * @param Float $amount
    // * @return void
    // */
    // public function getSessionLocaleAmountAttribute()
    // {
    //     if ($this->currency === $this->game->currency) {
    //         return $this->amount;
    //     }

    //     $currencyConverter = new CurrencyConverter();

    //     return $currencyConverter
    //             ->convertFrom($this->currency)
    //             ->convertTo($this->game->currency)
    //             ->convertAt($this->game->start_time)
    //             ->convert($this->amount);
    // }

    // /**
    // * Mutate amount in to user currency
    // *
    // * @param Float $amount
    // * @return void
    // */
    // public function getLocaleAmountAttribute()
    // {
    //     if ($this->currency === $this->game->user->currency) {
    //         return $this->amount;
    //     }

    //     $currencyConverter = new CurrencyConverter();

    //     return $currencyConverter
    //             ->convertFrom($this->currency)
    //             ->convertTo($this->game->user->currency)
    //             ->convertAt($this->game->start_time)
    //             ->convert($this->amount);
    // }
}
