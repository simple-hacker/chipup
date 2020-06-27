<?php

namespace App\Observers;

use App\Abstracts\GameTransaction;
use App\Currency\CurrencyConverter;

class GameTransactionObserver
{
    /**
     * Handle the transaction "saving" event.
     * This covers the case for both creating and updating
     * Calculate the locale amount and session locale amount
     * before persisting the game transaction
     *
     * @param  \App\GameTransaction  $transaction
     * @return void
     */
    public function saving(GameTransaction $transaction)
    {
        $currencyConverter = new CurrencyConverter();

        $locale_amount = $currencyConverter
                        ->convertFrom($transaction->currency)
                        ->convertTo($transaction->game->user->currency)
                        ->convertAt($transaction->game->start_time ?? now())
                        ->convert($transaction->amount);

        $transaction->locale_amount = $locale_amount;

        // Uses the same instance of currencyConverter
        // So convertFrom and convertAt have already been set.
        $session_locale_amount = $currencyConverter
                        ->convertTo($transaction->game->currency)
                        ->convert($transaction->amount);

        $transaction->session_locale_amount = $session_locale_amount;
    }
}
