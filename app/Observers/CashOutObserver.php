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
        tap($cashOut->game)->increment('profit', $cashOut->amount);
    }

    /**
     * Handle the cash out "updated" event.
     *
     * @param  \App\Transactions\CashOut  $cashOut
     * @return void
     */
    public function updated(CashOut $cashOut)
    {
        //
    }

    /**
     * Handle the cash out "deleted" event.
     *
     * @param  \App\Transactions\CashOut  $cashOut
     * @return void
     */
    public function deleted(CashOut $cashOut)
    {
        //
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
