<?php

namespace App\Observers;

use App\Abstracts\PositiveGameTransaction;

class PositiveGameTransactionObserver
{
    /**
     * Handle the positive game transaction "created" event.
     *
     * @param  \App\Abstract\Abstract\PositiveGameTransaction  $positiveGameTransaction
     * @return void
     */
    public function created(PositiveGameTransaction $positiveGameTransaction)
    {
        $positiveGameTransaction->game->increment('profit', $positiveGameTransaction->amount);
    }

    /**
     * Handle the positive game transaction "updated" event.
     *
     * @param  \App\Abstract\PositiveGameTransaction  $positiveGameTransaction
     * @return void
     */
    public function updated(PositiveGameTransaction $positiveGameTransaction)
    {
        // Find the difference needed for profit to be accurate.
        $amount = $positiveGameTransaction->amount - $positiveGameTransaction->getOriginal('amount');

        $positiveGameTransaction->game->increment('profit', $amount);
    }

    /**
     * Handle the positive game transaction "deleted" event.
     *
     * @param  \App\Abstract\PositiveGameTransaction  $positiveGameTransaction
     * @return void
     */
    public function deleted(PositiveGameTransaction $positiveGameTransaction)
    {
        // This is decrement because a PositiveGameTransaction is a increment normally, so when it's 
        // deleted we subtract the amount we put on.
        $positiveGameTransaction->game->decrement('profit', $positiveGameTransaction->amount);
    }

    /**
     * Handle the positive game transaction "restored" event.
     *
     * @param  \App\Abstract\PositiveGameTransaction  $positiveGameTransaction
     * @return void
     */
    public function restored(PositiveGameTransaction $positiveGameTransaction)
    {
        //
    }

    /**
     * Handle the positive game transaction "force deleted" event.
     *
     * @param  \App\Abstract\PositiveGameTransaction  $positiveGameTransaction
     * @return void
     */
    public function forceDeleted(PositiveGameTransaction $positiveGameTransaction)
    {
        //
    }
}
