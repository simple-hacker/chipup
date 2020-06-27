<?php

namespace App\Observers;

use App\Abstracts\NegativeGameTransaction;

class NegativeGameTransactionObserver
{
    /**
     * Handle the negative game transaction "created" event.
     *
     * @param  \App\NegativeGameTransaction  $negativeGameTransaction
     * @return void
     */
    public function created(NegativeGameTransaction $negativeGameTransaction)
    {

    }

    /**
     * Handle the negative game transaction "updated" event.
     *
     * @param  \App\NegativeGameTransaction  $negativeGameTransaction
     * @return void
     */
    public function updated(NegativeGameTransaction $negativeGameTransaction)
    {
        //
    }

    /**
     * Handle the negative game transaction "deleted" event.
     *
     * @param  \App\NegativeGameTransaction  $negativeGameTransaction
     * @return void
     */
    public function deleted(NegativeGameTransaction $negativeGameTransaction)
    {
        //
    }

    /**
     * Handle the negative game transaction "restored" event.
     *
     * @param  \App\NegativeGameTransaction  $negativeGameTransaction
     * @return void
     */
    public function restored(NegativeGameTransaction $negativeGameTransaction)
    {
        //
    }

    /**
     * Handle the negative game transaction "force deleted" event.
     *
     * @param  \App\NegativeGameTransaction  $negativeGameTransaction
     * @return void
     */
    public function forceDeleted(NegativeGameTransaction $negativeGameTransaction)
    {
        //
    }
}
