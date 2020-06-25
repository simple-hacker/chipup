<?php

namespace App\Abstracts;

use Money\Money;
use Money\Currency;
use Money\Converter;
use App\ExchangeRates;
use Money\Exchange\FixedExchange;
use Money\Currencies\ISOCurrencies;
use Money\Exchange\IndirectExchange;
use Illuminate\Database\Eloquent\Model;
use Money\Exchange\ReversedCurrenciesExchange;

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

        // TODO: Will need to do historial converting as well.
        // $rates will be closest to $this->date
        $rates = ExchangeRates::first();

        $exchange = new ReversedCurrenciesExchange(new FixedExchange([
            'GBP' => $rates->rates
        ]));

        $indirectExchange = new IndirectExchange($exchange, new ISOCurrencies);
        
        $converter = new Converter(new ISOCurrencies(), $indirectExchange);

        $transactionAmount = new Money($this->attributes['amount'], new Currency($this->currency));

        // Convert Transaction amount to currency of the session.
        $sessionLocaleAmount = $converter->convert($transactionAmount, new Currency($this->game->currency));
        return $sessionLocaleAmount->getAmount() / 100;
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

        // TODO: Will need to do historial converting as well.
        // $rates will be closest to $this->date
        $rates = ExchangeRates::first();

        $exchange = new ReversedCurrenciesExchange(new FixedExchange([
            'GBP' => $rates->rates
        ]));

        $indirectExchange = new IndirectExchange($exchange, new ISOCurrencies);
        
        $converter = new Converter(new ISOCurrencies(), $indirectExchange);

        $transactionAmount = new Money($this->attributes['amount'], new Currency($this->currency));

        // Convert Transaction amount to currency of the the user.
        $localeAmount = $converter->convert($transactionAmount, new Currency($this->game->user->currency));
        return $localeAmount->getAmount() / 100;
    }
}
