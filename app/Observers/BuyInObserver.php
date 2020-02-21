<?php

namespace App\Observers;

use App\BuyIn;

class BuyInObserver
{
    /**
     * Handle the buy in "created" event.
     *
     * @param  \App\BuyIn  $buyIn
     * @return void
     */
    public function created(BuyIn $buyIn)
    {
        tap($buyIn->game)->decrement('profit', $buyIn->amount);
    }

    /**
     * Handle the buy in "updated" event.
     *
     * @param  \App\BuyIn  $buyIn
     * @return void
     */
    public function updated(BuyIn $buyIn)
    {
        //
    }

    /**
     * Handle the buy in "deleted" event.
     *
     * @param  \App\BuyIn  $buyIn
     * @return void
     */
    public function deleted(BuyIn $buyIn)
    {
        //
    }

    /**
     * Handle the buy in "restored" event.
     *
     * @param  \App\BuyIn  $buyIn
     * @return void
     */
    public function restored(BuyIn $buyIn)
    {
        //
    }

    /**
     * Handle the buy in "force deleted" event.
     *
     * @param  \App\BuyIn  $buyIn
     * @return void
     */
    public function forceDeleted(BuyIn $buyIn)
    {
        //
    }
}
