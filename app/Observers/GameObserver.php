<?php

namespace App\Observers;

use App\Abstracts\Game;

class GameObserver
{
    /**
     * Handle the game "created" event.
     *
     * @param  \App\Abstracts\Game  $game
     * @return void
     */
    public function created(Game $game)
    {
        //
    }

    /**
     * Handle the game "updated" event.
     *
     * @param  \App\Abstracts\Game  $game
     * @return void
     */
    public function updated(Game $game)
    {
        // Get difference between the new $game->profit and the old profit if it's been changed
        if ($game->isDirty('profit')) {
            $game->user->updateBankroll($game->profit - ($game->getOriginal('profit') / 100));
        }
    }

    /**
     * Handle the game "deleting" event.
     *
     * @param  \App\Abstracts\Game  $game
     * @return void
     */
    public function deleting(Game $game)
    {
        // This is fired just before the $game is deleted so we still have access to it's profit
        // so we can update the user's bankroll.
        // We multiple the profit by -1
        // If the Game's profit was positive, need to subtract that amount from the bankroll
        // If the Game's profit was negative, need to add that amount to the bankroll.
        // $game->user->updateBankroll($game->profit * -1);
    }

    /**
     * Handle the game "restored" event.
     *
     * @param  \App\Abstracts\Game  $game
     * @return void
     */
    public function restored(Game $game)
    {
        //
    }

    /**
     * Handle the game "force deleted" event.
     *
     * @param  \App\Abstracts\Game  $game
     * @return void
     */
    public function forceDeleted(Game $game)
    {
        //
    }
}
