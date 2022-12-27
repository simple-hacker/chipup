<?php

namespace App\Abstracts;

use App\Currency\CurrencyConverter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

abstract class GameTransaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'float'
    ];

    /**
    * Returns the Transaction's game type model
    *
    * @return MorphTo
    */
    public function game()
    {
        return $this->morphTo();
    }

    /**
    * Return the User from belongsToThrough
    *
    * @param
    * @return MorphTo
    */
    public function user()
    {
        return $this->game->user;
    }

    /**
    * Mutate amount in to currency
    *
    * @param int $amount
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
    * @param int $locale_amount
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
    * @param int $session_locale_amount
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
}
