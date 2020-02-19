<?php

namespace App\Observers;

use App\BankrollTransaction;

class BankrollTransactionObserver
{
    /**
     * Handle the bankroll transaction "created" event.
     *
     * @param  \App\BankrollTransaction  $bankrollTransaction
     * @return void
     */
    public function created(BankrollTransaction $bankrollTransaction)
    {
        $bankrollTransaction->user->updateBankroll($bankrollTransaction->amount);
    }

    /**
     * Handle the bankroll transaction "updated" event.
     *
     * @param  \App\BankrollTransaction  $bankrollTransaction
     * @return void
     */
    public function updated(BankrollTransaction $bankrollTransaction)
    {
        // Find the difference needed for bankroll to be accurate.
        $amount = $bankrollTransaction->amount - $bankrollTransaction->getOriginal('amount');

        $bankrollTransaction->user->updateBankroll($amount);

    }

    /**
     * Handle the bankroll transaction "deleted" event.
     *
     * @param  \App\BankrollTransaction  $bankrollTransaction
     * @return void
     */
    public function deleted(BankrollTransaction $bankrollTransaction)
    {
        //
    }

    /**
     * Handle the bankroll transaction "restored" event.
     *
     * @param  \App\BankrollTransaction  $bankrollTransaction
     * @return void
     */
    public function restored(BankrollTransaction $bankrollTransaction)
    {
        //
    }

    /**
     * Handle the bankroll transaction "force deleted" event.
     *
     * @param  \App\BankrollTransaction  $bankrollTransaction
     * @return void
     */
    public function forceDeleted(BankrollTransaction $bankrollTransaction)
    {
        //
    }
}
