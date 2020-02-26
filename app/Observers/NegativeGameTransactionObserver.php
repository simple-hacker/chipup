<?php

namespace App\Observers;

use App\Abstracts\NegativeGameTransaction;

class NegativeGameTransactionObserver
{
    /**
     * Handle the negative game transaction "created" event.
     *
     * @param  \App\Abstracts\NegativeGameTransaction  $negativeGameTransaction
     * @return void
     */
    public function created(NegativeGameTransaction $negativeGameTransaction)
    {
        $negativeGameTransaction->game->profit -= $negativeGameTransaction->amount;
        $negativeGameTransaction->game->save();
    }

    /**
     * Handle the negative game transaction "updated" event.
     *
     * @param  \App\Abstracts\NegativeGameTransaction  $negativeGameTransaction
     * @return void
     */
    public function updated(NegativeGameTransaction $negativeGameTransaction)
    {
        // Find the difference needed for profit to be accurate.
        $amount = $negativeGameTransaction->amount - $negativeGameTransaction->getOriginal('amount');

        $negativeGameTransaction->game->profit -= $amount;
        $negativeGameTransaction->game->save();
    }

    /**
     * Handle the negative game transaction "deleted" event.
     *
     * @param  \App\Abstracts\NegativeGameTransaction  $negativeGameTransaction
     * @return void
     */
    public function deleted(NegativeGameTransaction $negativeGameTransaction)
    {
        // This is increment because a negativeGameTransaction is a decrement normally, so when it's 
        // deleted we add the amount back on.
        $negativeGameTransaction->game->profit += $negativeGameTransaction->amount;
        $negativeGameTransaction->game->save();
    }

    /**
     * Handle the negative game transaction "restored" event.
     *
     * @param  \App\Abstracts\NegativeGameTransaction  $negativeGameTransaction
     * @return void
     */
    public function restored(NegativeGameTransaction $negativeGameTransaction)
    {
        //
    }

    /**
     * Handle the negative game transaction "force deleted" event.
     *
     * @param  \App\Abstracts\NegativeGameTransaction  $negativeGameTransaction
     * @return void
     */
    public function forceDeleted(NegativeGameTransaction $negativeGameTransaction)
    {
        //
    }
}
