<?php

namespace App\Transactions;

use App\CurrencyConverter;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;

class Bankroll extends Model
{
    protected $guarded = [];

    protected $table = 'bankroll_transactions';

    protected $casts = [
        'user_id' => 'integer',
    ];

    protected $dates = [
        'date'
    ];

    protected $appends = ['locale_amount'];

    /**
    * Returns the user the Bankroll belongs to
    *
    * @return belongsTo
    */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
    * Mutate amount in to currency
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
    * Mutate amount in to locale currency
    *
    * @return void
    */
    public function getLocaleAmountAttribute()
    {
        if ($this->currency === $this->user->currency) {
            return $this->amount;
        }

        $currencyConverter = new CurrencyConverter();

        return $currencyConverter
                ->convertFrom($this->currency)
                ->convertTo($this->user->currency)
                ->convertAt($this->date)
                ->convert($this->amount);
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
