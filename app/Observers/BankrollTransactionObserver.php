<?php

namespace App\Observers;

use App\Transactions\Bankroll;
use App\Currency\CurrencyConverter;

class BankrollTransactionObserver
{
    /**
     * Handle the bankroll "saving" event.
     * This covers the case for both creating and updating
     * Calculate the locale amount before persisting the bankrollTransaction
     *
     * @param  \App\Bankroll  $bankrollTransaction
     * @return void
     */
    public function saving(Bankroll $bankrollTransaction)
    {
        $currencyConverter = new CurrencyConverter();

        $locale_amount = $currencyConverter
                        ->convertFrom($bankrollTransaction->currency)
                        ->convertTo($bankrollTransaction->user->currency)
                        ->convertAt($bankrollTransaction->date)
                        ->convert($bankrollTransaction->amount);

        $bankrollTransaction->locale_amount = $locale_amount;
    }
}
