<?php

namespace App;

use App\Exceptions\NonIntegerAmount;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
    * Updates the user's bankroll.
    * 
    * @param integer amount
    */
    public function updateBankroll($amount)
    {   
        if (!is_int($amount)) {
            throw new NonIntegerAmount;
        }

        if ($amount > 0) {
            $this->increment('bankroll', $amount);
        } else {
            $this->decrement('bankroll', $amount * -1);
        }
    }


    /**
    * Add amount to bankroll.
    * This updates the user's bankroll and creates and BankrollTransaction.
    * The bankroll is updated with a BankrollTransaction model observer in the created method.
    * 
    * @param integer amount
    * @return void
    */
    public function addToBankroll($amount)
    {
        if (!is_int($amount)) {
            throw new NonIntegerAmount;
        }

        BankrollTransaction::create([
            'user_id' => $this->id,
            'amount' => $amount
        ]);
    }


    /**
    * Withdraw amount from bankroll.
    * This updates the user's bankroll and creates and BankrollTransaction.
    * The bankroll is updated with a BankrollTransaction model observer in the created method.
    * 
    * @param integer amount
    * @return void
    */
    public function withdrawFromBankroll($amount)
    {
        if (!is_int($amount)) {
            throw new NonIntegerAmount;
        }

        BankrollTransaction::create([
            'user_id' => $this->id,
            'amount' => $amount * -1
        ]);
    }


    /**
    * Returns the user's bankroll transactions
    *
    * @return hasMany
    */
    public function bankrollTransactions()
    {
        return $this->hasMany('App\BankrollTransaction');
    }
}
