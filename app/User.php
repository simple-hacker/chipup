<?php

namespace App;

use Illuminate\Support\Carbon;
use App\Exceptions\NonIntegerAmount;
use App\Exceptions\CashGameInProgress;
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


    /**
    * Returns a collection of the user's CashGames
    * 
    * @return hasMany
    */
    public function cashGames()
    {
        return $this->hasMany('App\CashGame');
    }

    /**
    * Start a Cash Game with the current time as start_time
    * This first checks if we have a Cash Game in progress by checking the count of CashGame where end_time is null
    *
    * @return \App\CashGame
    */
    public function startCashGame(Carbon $start_time = null) : CashGame
    {
        $count = $this->cashGames()
                        ->where('end_time', null)
                        ->orderByDesc('start_time')
                        ->count();

        if ($count > 0) {
            throw new CashGameInProgress;
        }

        return $this->cashGames()->create([
            'start_time' => $start_time ?? now()
        ]);
    }

    /**
    * Return the latest Cash Game without an end_time.
    * NOTE: We should only ever have one live session so need to check.
    * This check is performed in startCashGame
    * 
    * @return \App\CashGame
    */
    public function currentLiveCashGame() : ?CashGame
    {
        return $this->cashGames()
                    ->where('end_time', null)
                    ->orderByDesc('start_time')
                    ->first();
    }
}
