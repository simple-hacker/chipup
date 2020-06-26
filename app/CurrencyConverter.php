<?php

namespace App;

use Money\Money;
use Money\Currency;
use Money\Converter;
use App\ExchangeRates;
use Illuminate\Support\Carbon;
use Money\Exchange\FixedExchange;
use Money\Currencies\ISOCurrencies;
use Money\Exchange\IndirectExchange;
use Illuminate\Support\Facades\Cache;
use phpDocumentor\Reflection\Types\Integer;
use Money\Exchange\ReversedCurrenciesExchange;

class CurrencyConverter{

    private $amount;
    private $baseCurrency;
    private $targetCurrency;
    private $date;

    /**
    * Construct the CurrencyConvert class
    * 
    * @return void
    */
    public function __construct()
    {
        $this->amount = 0;
        $this->date = Carbon::today();
        $this->baseCurrency = new Currency('GBP');
        $this->targetCurrency = new Currency('GBP');
    }

    /**
    * Set the target currency
    * 
    * @param String $currency
    * @return Self
    */
    public function convertFrom(String $currency)
    {
        $this->baseCurrency = new Currency($currency);
        return $this;
    }

    /**
    * Set the target currency
    * 
    * @param String $currency
    * @return Self
    */
    public function convertTo(String $currency)
    {
        $this->targetCurrency = new Currency($currency);
        return $this;
    }

    /**
    * Convert at rate on specific day
    * 
    * @param Carbon $date
    * @return Self
    */
    public function convertAt(Carbon $date)
    {
        $this->date = $date;
        return $this;
    }

    /**
    * Convert desirred amount from baseCurrency to targetCurrency
    * using rates found at convertAt date
    * 
    * @param Integer $amount
    * @return Float
    */
    public function convert($amount = 0)
    {
        if ($this->baseCurrency->equals($this->targetCurrency)) {
            return $amount;
        }

        $exchange = new ReversedCurrenciesExchange(new FixedExchange([
            'GBP' =>  $this->getRates()
        ]));

        $indirectExchange = new IndirectExchange($exchange, new ISOCurrencies);
        
        $converter = new Converter(new ISOCurrencies(), $indirectExchange);

        $transactionAmount = new Money($amount * 100, $this->baseCurrency);

        // Convert Transaction amount to currency of the the user.
        $convertedAmount = $converter->convert($transactionAmount, $this->targetCurrency);

        return $convertedAmount->getAmount() / 100;
    }

    /**
    * Returns the closest exchange rates for the given date
    * Returns the cache value for better performance
    * Earliest exchange rate is 2018-01-01
    * All rates are in base GBP
    * 
    * @return Array
    */
    private function getRates()
    {
        $cacheKey = 'exchange_rates_' . $this->date->toDateString();

        $exchangeRates = Cache::rememberForever($cacheKey, function () {
            $closestExchangeRates = ExchangeRates::whereDate('date', '<=', $this->date)->orderByDesc('date')->first();

            // If requested date is less than 2018-01-01 then use rates for 2018-01-01
            if (! $closestExchangeRates) {
                $closestExchangeRates = ExchangeRates::orderBy('date')->first();
            }
    
            return $closestExchangeRates->rates;
        });

        return $exchangeRates;
    }
}