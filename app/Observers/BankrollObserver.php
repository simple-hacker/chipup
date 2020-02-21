<?php

namespace App\Observers;

use App\Transactions\Bankroll;

class BankrollObserver
{
    /**
     * Handle the bankroll transaction "created" event.
     *
     * @param  \App\Transactions\Bankroll  $bankroll
     * @return void
     */
    public function created(Bankroll $bankroll)
    {
        $bankroll->user->updateBankroll($bankroll->amount);
    }

    /**
     * Handle the bankroll transaction "updated" event.
     *
     * @param  \App\Transactions\Bankroll  $bankroll
     * @return void
     */
    public function updated(Bankroll $bankroll)
    {
        // Find the difference needed for bankroll to be accurate.
        $amount = $bankroll->amount - $bankroll->getOriginal('amount');

        $bankroll->user->updateBankroll($amount);

    }

    /**
     * Handle the bankroll transaction "deleted" event.
     *
     * @param  \App\Transactions\Bankroll  $bankroll
     * @return void
     */
    public function deleted(Bankroll $bankroll)
    {
        //
    }

    /**
     * Handle the bankroll transaction "restored" event.
     *
     * @param  \App\Transactions\Bankroll  $bankroll
     * @return void
     */
    public function restored(Bankroll $bankroll)
    {
        //
    }

    /**
     * Handle the bankroll transaction "force deleted" event.
     *
     * @param  \App\Transactions\Bankroll  $bankroll
     * @return void
     */
    public function forceDeleted(Bankroll $bankroll)
    {
        //
    }
}
