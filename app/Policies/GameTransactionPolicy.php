<?php

namespace App\Policies;

use App\Abstracts\GameTransaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GameTransactionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can manage the game.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Abstracts\Game  $game
     * @return mixed
     */
    public function manage(User $user, GameTransaction $game_transaction)
    {
        return $user->id === $game_transaction->user()->id;
    }
}
