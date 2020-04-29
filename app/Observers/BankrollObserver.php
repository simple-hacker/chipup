<?php

namespace App\Observers;

use App\Transactions\Bankroll;

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
        $bankrollTransaction->user->updateBankroll($bankrollTransaction->amount);
    }

    /**
     * Handle the bankroll transaction "updated" event.
     *
     * @param  \App\Transactions\Bankroll  $bankrollTransaction
     * @return void
     */
    public function updated(Bankroll $bankrollTransaction)
    {
        // Find the difference needed for bankroll to be accurate.
        $amount = $bankrollTransaction->amount - ($bankrollTransaction->getOriginal('amount') / 100);

        $bankrollTransaction->user->updateBankroll($amount);
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
        
        $bankrollTransaction->user->updateBankroll($bankrollTransaction->amount * -1);
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
