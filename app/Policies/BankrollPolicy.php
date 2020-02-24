<?php

namespace App\Policies;

use App\Transactions\Bankroll;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BankrollPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the bankroll.
     *
     * @param  \App\User  $user
     * @param  \App\Bankroll  $bankrollTransaction
     * @return mixed
     */
    public function update(User $user, Bankroll $bankrollTransaction)
    {
        return $user->id === $bankrollTransaction->user_id;
    }

    /**
     * Determine whether the user can delete the bankroll.
     *
     * @param  \App\User  $user
     * @param  \App\Bankroll  $bankrollTransaction
     * @return mixed
     */
    public function delete(User $user, Bankroll $bankrollTransaction)
    {
        return $user->id === $bankrollTransaction->user_id;
    }
}
