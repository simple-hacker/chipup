<?php

namespace App\Transactions;

use App\Currency\CurrencyConverter;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;

class Bankroll extends Model
{
    protected $guarded = [];

    protected $table = 'bankroll_transactions';

    protected $casts = [
        'user_id' => 'integer',
    ];

    protected $dates = ['date'];

    /**
    * Returns the user the Bankroll belongs to
    *
    * @return belongsTo
    */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
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
    * @param Integer $amount
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
    * Mutate date to be a Carbon instance to UTC
    * So can pass in values like 2020-03-01T16:45:21.000Z
    * and doesn't fail on MySQL timestamp column
    *
    * @param String $date
    * @return void
    */
    public function setDateAttribute($date)
    {
        if ($date) {
            $this->attributes['date'] = Carbon::create($date)->startOfDay();
        } else {
            $this->attributes['date'] = now()->startOfDay();
        }
    }
}
