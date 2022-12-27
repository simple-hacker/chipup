<?php

namespace App\Policies;

use App\Abstracts\Game;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GamePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can manage the game.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Game  $game
     * @return mixed
     */
    public function manage(User $user, Game $game)
    {
        return $user->id === $game->user_id;
    }
}
