<?php

namespace App\Transactions;

use Money\Money;
use Money\Currency;
use Money\Converter;

use App\ExchangeRates;
use Illuminate\Support\Carbon;
use Money\Exchange\FixedExchange;
use Money\Currencies\ISOCurrencies;
use Money\Exchange\IndirectExchange;
use Illuminate\Database\Eloquent\Model;
use Money\Exchange\ReversedCurrenciesExchange;

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
        // TODO: Will need to do historial converting as well.
        // $rates will be closest to $this->date
        $rates = ExchangeRates::first();

        $exchange = new ReversedCurrenciesExchange(new FixedExchange([
            'GBP' => $rates->rates
        ]));

        $indirectExchange = new IndirectExchange($exchange, new ISOCurrencies);
        
        $converter = new Converter(new ISOCurrencies(), $indirectExchange);

        // attributes['amount'] is the raw amount from database before being divided by 100, as Money needs an integer
        $transactionAmount = new Money($this->attributes['amount'], new Currency($this->currency));

        $localeAmount = $converter->convert($transactionAmount, new Currency($this->user->currency));
        return $localeAmount->getAmount() / 100;
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
