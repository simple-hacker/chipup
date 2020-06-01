<?php

namespace App;

use App\Transactions\Bankroll;
use Illuminate\Support\Carbon;
use App\Exceptions\CashGameInProgress;
use App\Exceptions\TournamentInProgress;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

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
        'setup_complete' => 'boolean',
        'bankroll' => 'integer',
        'default_stake_id' => 'integer',
        'default_limit_id' => 'integer',
        'default_variant_id' => 'integer',
        'default_table_size_id' => 'integer',
    ];

    /**
    * Returns a collection of the user's social logins.
    * 
    * @return hasMany
    */
    public function socialLogins()
    {
        return $this->hasMany('App\SocialLogin');
    }

    /**
    * Updates the user's bankroll.
    * 
    * @param integer amount
    */
    public function updateBankroll($amount)
    {   
        if ($amount > 0) {
            $this->increment('bankroll', $amount * 100);
        } else {
            $this->decrement('bankroll', $amount * -100);
        }
    }

    /**
    * Add amount to bankroll.
    * This updates the user's bankroll and creates and Bankroll.
    * The bankroll is updated with a Bankroll model observer in the created method.
    * 
    * @param integer amount
    * @return void
    */
    public function createBankrollTransaction($transaction)
    {
        return $this->bankrollTransactions()->create([
            'date' => $transaction['date'] ?? null,
            'amount' => $transaction['amount'],
            'comments' => $transaction['comments'] ?? null,
        ]);
    }

    /**
    * Returns the user's bankroll transactions
    *
    * @return hasMany
    */
    public function bankrollTransactions()
    {
        return $this->hasMany('App\Transactions\Bankroll')->orderByDesc('date');
    }

    /**
    * Returns a collection of the user's CashGames
    * Unable to prepare route [api/user] for serialization. Uses Closure.Unable to prepare route [api/user] for serialization. Uses Closure.
    * @return hasMany
    */
    public function cashGames()
    {
        return $this->hasMany('App\CashGame')->orderByDesc('start_time');
    }

    /**
    * Start a Cash Game with the current time as start_time
    * This first checks if we have a Cash Game in progress by checking the count of CashGame where end_time is null
    *
    * @param array $attributes
    * @return \App\CashGame
    */
    public function startCashGame(Array $attributes = []) : CashGame
    {
        // The $attributes array will have been validated in the CashGameController

        if ($this->liveCashGame()) {
            throw new CashGameInProgress('A Cash Game is already in progress.');
        }

        // All default values attributes are required in the StartCashGameRequest
        // Using defaults if not set in case mistakes have been made elsewhere in app.
        return $this->cashGames()->create([
            'start_time' => $attributes['start_time'] ?? null,
            'stake_id' => $attributes['stake_id'] ?? $this->default_stake_id,
            'variant_id' => $attributes['variant_id'] ?? $this->default_variant_id,
            'limit_id' => $attributes['limit_id'] ?? $this->default_limit_id,
            'table_size_id' => $attributes['table_size_id'] ?? $this->default_table_size_id,
            'location' => $attributes['location'] ?? $this->default_location,
            'comments' => $attributes['comments'] ?? null,
        ]);
    }

    /**
    * Return the latest Cash Game without an end_time.
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
    * Returns a collection of Cash Games where provided time is between start_time and end_time
    * This is used to prevent adding a completed cash game between an already existing cash game.
    * 
    * @param Carbon $time
    * @return hasMany
    */
    public function cashGamesAtTime($time = null)
    {
        $start_time = $time ? Carbon::create($time) : now();
        
        return $this->cashGames()
                    ->where('start_time', '<=', $start_time)
                    ->where(function ($query) use ($start_time) {
                        $query->where('end_time', '>=', $start_time)
                              ->orWhere('end_time', null);
                    })
                    ->count();
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
    * @param Array $attributes
    * @return \App\Tournament
    */
    public function startTournament(Array $attributes = []) : Tournament
    {
        // The $attributes array will have been validated in the TournamentController
        
        if ($this->liveTournament()) {
            throw new TournamentInProgress('A Tournament is already in progress.');
        }

        return $this->tournaments()->create([
            'start_time' => $attributes['start_time'] ?? null,
            'buy_in' => $attributes['amount'],
            'name' => $attributes['name'] ?? null,
            'variant_id' => $attributes['variant_id'],
            'limit_id' => $attributes['limit_id'],
            'entries' => $attributes['entries'] ?? null,
            'location' => $attributes['location'] ?? null,
            'comments' => $attributes['comments'] ?? null,
        ]);
    }

    /**
    * Return the latest Tournament without an end_time.
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

    /**
    * Return the user's default stake model
    * 
    * @return hasOne
    */
    public function default_stake()
    {
        return $this->hasOne('App\Attributes\Stake', 'id', 'default_stake_id');
    }

    /**
    * Return the user's default variant model
    * 
    * @return hasOne
    */
    public function default_variant()
    {
        return $this->hasOne('App\Attributes\Variant', 'id', 'default_variant_id');
    }

    /**
    * Return the user's default limit model
    * 
    * @return hasOne
    */
    public function default_limit()
    {
        return $this->hasOne('App\Attributes\Limit', 'id', 'default_limit_id');
    }

    /**
    * Return the user's default table size model
    * 
    * @return hasOne
    */
    public function default_table_size()
    {
        return $this->hasOne('App\Attributes\TableSize', 'id', 'default_table_size_id');
    }

    /**
    * Boolean if user has completed the account setup.
    * 
    * @return 
    */
    public function completeSetup(): User
    {
        $this->update([
            'setup_complete' => true
        ]);

        return $this;
    }

    /**
    * Mutate bankroll in to currency
    *
    * @param Integer $bankroll
    * @return void
    */
    public function getBankrollAttribute($bankroll)
    {
        return $bankroll / 100;
    }

    /**
    * Mutate bankroll in to lowest denomination
    *
    * @param Float $bankroll
    * @return void
    */
    public function setBankrollAttribute($bankroll)
    {
        $this->attributes['bankroll'] = $bankroll * 100;
    }
}
