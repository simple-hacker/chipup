<?php

namespace App\Observers;

use App\Transactions\CashOut;

class CashOutObserver
{
    /**
     * Handle the cash out "created" event.
     *
     * @param  \App\Transactions\CashOut  $cashOut
     * @return void
     */
    public function created(CashOut $cashOut)
    {
        $cashOut->game->increment('profit', $cashOut->amount);
    }

    /**
     * Handle the cash out "updated" event.
     *
     * @param  \App\Transactions\CashOut  $cashOut
     * @return void
     */
    public function updated(CashOut $cashOut)
    {
        // Find the difference needed for profit to be accurate.
        $amount = $cashOut->amount - $cashOut->getOriginal('amount');

        $cashOut->game->increment('profit', $amount);
    }

    /**
     * Handle the cash out "deleted" event.
     *
     * @param  \App\Transactions\CashOut  $cashOut
     * @return void
     */
    public function deleted(CashOut $cashOut)
    {
        // This is decrement because a CashOut is a increment normally, so when it's 
        // deleted we subtract the amount we put on.
        $cashOut->game->decrement('profit', $cashOut->amount);
    }

    /**
     * Handle the cash out "restored" event.
     *
     * @param  \App\Transactions\CashOut  $cashOut
     * @return void
     */
    public function restored(CashOut $cashOut)
    {
        //
    }

    /**
     * Handle the cash out "force deleted" event.
     *
     * @param  \App\Transactions\CashOut  $cashOut
     * @return void
     */
    public function forceDeleted(CashOut $cashOut)
    {
        //
    }
}
