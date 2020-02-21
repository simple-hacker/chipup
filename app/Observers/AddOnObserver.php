<?php

namespace App\Observers;

use App\Transactions\AddOn;

class AddOnObserver
{
    /**
     * Handle the add on "created" event.
     *
     * @param  \App\Transactions\AddOn  $addOn
     * @return void
     */
    public function created(AddOn $addOn)
    {
        $addOn->game->decrement('profit', $addOn->amount);
    }

    /**
     * Handle the add on "updated" event.
     *
     * @param  \App\Transactions\AddOn  $addOn
     * @return void
     */
    public function updated(AddOn $addOn)
    {
        // Find the difference needed for profit to be accurate.
        $amount = $addOn->amount - $addOn->getOriginal('amount');

        $addOn->game->decrement('profit', $amount);
    }

    /**
     * Handle the add on "deleted" event.
     *
     * @param  \App\Transactions\AddOn  $addOn
     * @return void
     */
    public function deleted(AddOn $addOn)
    {
        // This is increment because a add on is a decrement normally, so when it's 
        // deleted we add the amount back on.
        $addOn->game->increment('profit', $addOn->amount);
    }

    /**
     * Handle the add on "restored" event.
     *
     * @param  \App\Transactions\AddOn  $addOn
     * @return void
     */
    public function restored(AddOn $addOn)
    {
        //
    }

    /**
     * Handle the add on "force deleted" event.
     *
     * @param  \App\Transactions\AddOn  $addOn
     * @return void
     */
    public function forceDeleted(AddOn $addOn)
    {
        //
    }
}
