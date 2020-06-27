<?php

namespace App\Observers;

use App\Abstracts\PositiveGameTransaction;

class PositiveGameTransactionObserver
{
    /**
     * Handle the positive game transaction "created" event.
     *
     * @param  \App\PositiveGameTransaction  $positiveGameTransaction
     * @return void
     */
    public function created(PositiveGameTransaction $positiveGameTransaction)
    {
        
    }

    /**
     * Handle the positive game transaction "updated" event.
     *
     * @param  \App\PositiveGameTransaction  $positiveGameTransaction
     * @return void
     */
    public function updated(PositiveGameTransaction $positiveGameTransaction)
    {
        //
    }

    /**
     * Handle the positive game transaction "deleted" event.
     *
     * @param  \App\PositiveGameTransaction  $positiveGameTransaction
     * @return void
     */
    public function deleted(PositiveGameTransaction $positiveGameTransaction)
    {
        //
    }

    /**
     * Handle the positive game transaction "restored" event.
     *
     * @param  \App\PositiveGameTransaction  $positiveGameTransaction
     * @return void
     */
    public function restored(PositiveGameTransaction $positiveGameTransaction)
    {
        //
    }

    /**
     * Handle the positive game transaction "force deleted" event.
     *
     * @param  \App\PositiveGameTransaction  $positiveGameTransaction
     * @return void
     */
    public function forceDeleted(PositiveGameTransaction $positiveGameTransaction)
    {
        //
    }
}
