<?php

namespace App\Observers;

use App\Transactions\BuyIn;

class BuyInObserver
{
    /**
     * Handle the buy in "created" event.
     *
     * @param  \App\Transactions\BuyIn  $buyIn
     * @return void
     */
    public function created(BuyIn $buyIn)
    {
        $buyIn->game->decrement('profit', $buyIn->amount);
    }

    /**
     * Handle the buy in "updated" event.
     *
     * @param  \App\Transactions\BuyIn  $buyIn
     * @return void
     */
    public function updated(BuyIn $buyIn)
    {
        // Find the difference needed for profit to be accurate.
        $amount = $buyIn->amount - $buyIn->getOriginal('amount');

        $buyIn->game->decrement('profit', $amount);
    }

    /**
     * Handle the buy in "deleted" event.
     *
     * @param  \App\Transactions\BuyIn  $buyIn
     * @return void
     */
    public function deleted(BuyIn $buyIn)
    {
        // This is increment because a BuyIn is a decrement normally, so when it's 
        // deleted we add the amount back on.
        $buyIn->game->increment('profit', $buyIn->amount);
    }

    /**
     * Handle the buy in "restored" event.
     *
     * @param  \App\Transactions\BuyIn  $buyIn
     * @return void
     */
    public function restored(BuyIn $buyIn)
    {
        //
    }

    /**
     * Handle the buy in "force deleted" event.
     *
     * @param  \App\Transactions\BuyIn  $buyIn
     * @return void
     */
    public function forceDeleted(BuyIn $buyIn)
    {
        //
    }
}
