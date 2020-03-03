<?php

namespace App;

use App\Transactions\Bankroll;
use Illuminate\Support\Carbon;
use App\Exceptions\NonIntegerAmount;
use App\Exceptions\CashGameInProgress;
use App\Exceptions\TournamentInProgress;
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

        return $this->bankroll;
    }


    /**
    * Add amount to bankroll.
    * This updates the user's bankroll and creates and Bankroll.
    * The bankroll is updated with a Bankroll model observer in the created method.
    * 
    * @param integer amount
    * @return void
    */
    public function addToBankroll($amount)
    {
        if (!is_int($amount)) {
            throw new NonIntegerAmount;
        }

        return Bankroll::create([
            'user_id' => $this->id,
            'amount' => $amount
        ]);
    }


    /**
    * Withdraw amount from bankroll.
    * This updates the user's bankroll and creates and Bankroll.
    * The bankroll is updated with a Bankroll model observer in the created method.
    * 
    * @param integer amount
    * @return void
    */
    public function withdrawFromBankroll($amount)
    {
        if (!is_int($amount)) {
            throw new NonIntegerAmount;
        }

        return Bankroll::create([
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
        return $this->hasMany('App\Transactions\Bankroll');
    }


    /**
    * Returns a collection of the user's CashGames
    * Unable to prepare route [api/user] for serialization. Uses Closure.Unable to prepare route [api/user] for serialization. Uses Closure.
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
        if ($this->liveCashGame()) {
            throw new CashGameInProgress('A Cash Game is already in progress.');
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
    public function liveCashGame() : ?CashGame
    {
        return $this->cashGames()
                    ->where('end_time', null)
                    ->orderByDesc('start_time')
                    ->first();
    }

    /**
    * Returns a collection of the user's Tournaments
    * 
    * @return hasMany
    */
    public function tournaments()
    {
        return $this->hasMany('App\Tournament');
    }

    /**
    * Start a Tournament with the current time as start_time
    * This first checks if we have a Tournament in progress by checking the count of Tournament where end_time is null
    *
    * @return \App\Tournament
    */
    public function startTournament(Carbon $start_time = null) : Tournament
    {
        if ($this->liveTournament()) {
            throw new TournamentInProgress;
        }

        return $this->tournaments()->create([
            'start_time' => $start_time ?? now()
        ]);
    }

    /**
    * Return the latest Tournament without an end_time.
    * NOTE: We should only ever have one live tournament so need to check.
    * This check is performed in startTournament
    * 
    * @return \App\Tournament
    */
    public function liveTournament() : ?Tournament
    {
        return $this->tournaments()
                    ->where('end_time', null)
                    ->orderByDesc('start_time')
                    ->first();
    }
}
