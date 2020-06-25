<?php

namespace App\Observers;

use Money\Money;
use Money\Currency;
use Money\Converter;
use App\ExchangeRates;
use App\Transactions\Bankroll;
use Money\Exchange\FixedExchange;
use Money\Currencies\ISOCurrencies;
use Money\Exchange\IndirectExchange;
use Money\Exchange\ReversedCurrenciesExchange;

class BankrollObserver
{
    /**
     * Handle the bankroll transaction "created" event.
     *
     * @param  \App\Transactions\Bankroll  $bankrollTransaction
     * @return void
     */
    public function created(Bankroll $bankrollTransaction)
    {
        // $bankrollTransaction->user->updateBankroll($bankrollTransaction->locale_amount);
    }

    /**
     * Handle the bankroll transaction "updated" event.
     *
     * @param  \App\Transactions\Bankroll  $bankrollTransaction
     * @return void
     */
    public function updated(Bankroll $bankrollTransaction)
    {
        // TODO:  NEED TO CHECK IN CASE USER SWITCHES CURRENCY

        // // Find the difference needed for bankroll to be accurate.
        // $rates = ExchangeRates::first();
        // $exchange = new ReversedCurrenciesExchange(new FixedExchange([
        //     'GBP' => $rates->rates
        // ]));
        // $indirectExchange = new IndirectExchange($exchange, new ISOCurrencies);
        // $converter = new Converter(new ISOCurrencies(), $indirectExchange);


        // $originalLocaleAmount = new Money($bankrollTransaction->getOriginal('amount'), new Currency($bankrollTransaction->getOriginal('currency')));
        // $originalLocaleAmount = $converter->convert($originalLocaleAmount, new Currency($bankrollTransaction->user->currency));

        // $newLocaleAmount = new Money(($bankrollTransaction->amount * 100), new Currency($bankrollTransaction->currency));
        // $newLocaleAmount = $converter->convert($newLocaleAmount, new Currency($bankrollTransaction->user->currency));

        // $amount = ($newLocaleAmount->getAmount() - $originalLocaleAmount->getAmount()) / 100;

        // $bankrollTransaction->user->updateBankroll($amount);
    }

    /**
     * Handle the bankroll transaction "deleted" event.
     *
     * @param  \App\Transactions\Bankroll  $bankrollTransaction
     * @return void
     */
    public function deleted(Bankroll $bankrollTransaction)
    {
        // We update the User's bankroll by the opposite amount (multiply by -1)
        // So if we originally added 10000, we decrement by -10000 and vice versa
        
        // $bankrollTransaction->user->updateBankroll($bankrollTransaction->locale_amount * -1);
    }

    /**
     * Handle the bankroll transaction "restored" event.
     *
     * @param  \App\Transactions\Bankroll  $bankrollTransaction
     * @return void
     */
    public function restored(Bankroll $bankrollTransaction)
    {
        //
    }

    /**
     * Handle the bankroll transaction "force deleted" event.
     *
     * @param  \App\Transactions\Bankroll  $bankrollTransaction
     * @return void
     */
    public function forceDeleted(Bankroll $bankrollTransaction)
    {
        //
    }
}
