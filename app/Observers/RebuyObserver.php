<?php

namespace App\Observers;

use App\Transactions\Rebuy;

class RebuyObserver
{
    /**
     * Handle the rebuy "created" event.
     *
     * @param  \App\Transactions\Rebuy  $rebuy
     * @return void
     */
    public function created(Rebuy $rebuy)
    {
        $rebuy->game->decrement('profit', $rebuy->amount);
    }

    /**
     * Handle the rebuy "updated" event.
     *
     * @param  \App\Transactions\Rebuy  $rebuy
     * @return void
     */
    public function updated(Rebuy $rebuy)
    {
        // Find the difference needed for profit to be accurate.
        $amount = $rebuy->amount - $rebuy->getOriginal('amount');

        $rebuy->game->decrement('profit', $amount);
    }

    /**
     * Handle the rebuy "deleted" event.
     *
     * @param  \App\Transactions\Rebuy  $rebuy
     * @return void
     */
    public function deleted(Rebuy $rebuy)
    {
        // This is increment because a rebuy is a decrement normally, so when it's 
        // deleted we add the amount back on.
        $rebuy->game->increment('profit', $rebuy->amount);
    }

    /**
     * Handle the rebuy "restored" event.
     *
     * @param  \App\Transactions\Rebuy  $rebuy
     * @return void
     */
    public function restored(Rebuy $rebuy)
    {
        //
    }

    /**
     * Handle the rebuy "force deleted" event.
     *
     * @param  \App\Transactions\Rebuy  $rebuy
     * @return void
     */
    public function forceDeleted(Rebuy $rebuy)
    {
        //
    }
}
