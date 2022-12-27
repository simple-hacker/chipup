<?php

namespace App\Policies;

use App\Models\User;
use App\Attributes\Stake;
use Illuminate\Auth\Access\HandlesAuthorization;

class StakePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can manage the stake.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Attributes\Stake  $stake
     * @return mixed
     */
    public function manage(User $user, Stake $stake)
    {
        return $user->id === $stake->user_id;
    }
}
