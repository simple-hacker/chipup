<?php

namespace App\Models;

use App\Attributes\Stake;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Collection;
use App\Notifications\PokerVerifyEmail;
use Illuminate\Notifications\Notifiable;
use App\Exceptions\SessionInProgressException;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use HasApiTokens;
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
        'setup_complete' => 'boolean',
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
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification() {
        $this->notify(new PokerVerifyEmail);
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
            'currency' => $transaction['currency'] ?? $this->currency,
            'amount' => $transaction['amount'] ?? 0,
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
    * Return the latest Cash Game or Tournament without an end_time.
    *
    * @return Collection|null
    */
    public function liveSession()
    {
        $liveCashGame = $this->liveCashGame();
        $liveTournament = $this->liveTournament();

        return $liveCashGame ?? $liveTournament ?? [];
    }

    /**
    * Returns a collection of the user's CashGames
    * Unable to prepare route [api/user] for serialization. Uses Closure.Unable to prepare route [api/user] for serialization. Uses Closure.
    * @return hasMany
    */
    public function cashGames()
    {
        return $this->hasMany('App\Models\CashGame')->orderByDesc('start_time');
    }

    /**
    * Start a Cash Game with the current time as start_time
    * This first checks if we have a Session in progress and then checks if the start time would clash with another Cash Game.
    *
    * @param array $attributes
    * @return \App\Models\CashGame
    */
    public function startCashGame(Array $attributes = []) : CashGame
    {
        // The $attributes array will have been validated in the CashGameController

        if ($this->liveSession()) {
            throw new SessionInProgressException('A live session is already in progress.', 422);
        }

        // If start_time was provided check to see if it clashes with another tournament
        if (isset($attributes['start_time']) && ($this->cashGamesAtTime($attributes['start_time']) > 0)) {
            throw new \Exception('You already have another tournament at that time.', 422);
        }

        // All default values attributes are required in the StartCashGameRequest
        // Using defaults if not set in case mistakes have been made elsewhere in app.
        return $this->cashGames()->create([
            'start_time' => $attributes['start_time'] ?? null,
            'currency' => $attributes['currency'] ?? $this->currency ?? 'GBP',
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
    * @return \App\Models\CashGame|null
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
    * @param string $time
    * @return integer
    */
    public function cashGamesAtTime($time = null, $id = null) : int
    {
        $start_time = $time ? Carbon::create($time) : now();

        return $this->cashGames()
                    ->where('id', '<>', $id)
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
        return $this->hasMany('App\Models\Tournament')->orderByDesc('start_time');;
    }

    /**
    * Start a Tournament with the current time as start_time
    * This first checks if we have a Session in progress and then checks if the start time would clash with another Tournament.
    *
    * @param Array $attributes
    * @return \App\Models\Tournament
    */
    public function startTournament(Array $attributes = []) : Tournament
    {
        // The $attributes array will have been validated in the TournamentController
        if ($this->liveSession()) {
            throw new SessionInProgressException('A live session is already in progress.', 422);
        }

        // If start_time was provided check to see if it clashes with another tournament
        if (isset($attributes['start_time']) && ($this->tournamentsAtTime($attributes['start_time']) > 0)) {
            throw new \Exception('You already have another tournament at that time.', 422);
        }

        return $this->tournaments()->create([
            'start_time' => $attributes['start_time'] ?? null,
            'currency' => $attributes['currency'] ?? $this->currency ?? 'GBP',
            'name' => $attributes['name'] ?? null,
            'limit_id' => $attributes['limit_id'] ?? $this->default_limit_id,
            'variant_id' => $attributes['variant_id'] ?? $this->default_variant_id,
            'prize_pool' => $attributes['prize_pool'] ?? 0,
            'position' => $attributes['position'] ?? 0,
            'entries' => $attributes['entries'] ?? 0,
            'location' => $attributes['location'] ?? null,
            'comments' => $attributes['comments'] ?? null,
        ]);
    }

    /**
    * Return the latest Tournament without an end_time.
    * This check is performed in startTournament
    *
    * @return \App\Models\Tournament
    */
    public function liveTournament() : ?Tournament
    {
        return $this->tournaments()
                    ->where('end_time', null)
                    ->orderByDesc('start_time')
                    ->first();
    }

    /**
    * Returns a collection of Tournaments where provided time is between start_time and end_time
    * This is used to prevent adding a completed tournament between an already existing tournament.
    *
    * @param string $time
    * @return int
    */
    public function tournamentsAtTime($time = null, $id = null) : int
    {
        $start_time = $time ? Carbon::create($time) : now();

        return $this->tournaments()
                    ->where('id', '<>', $id)
                    ->where('start_time', '<=', $start_time->toDateTimeString())
                    ->where(function ($query) use ($start_time) {
                        $query->where('end_time', '>=', $start_time->toDateTimeString())
                              ->orWhere('end_time', null);
                    })
                    ->count();
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
    * @return App\Models\User
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
    * @return int
    */
    public function getBankrollAttribute()
    {
        return $this->totalBankrollTransactionAmounts() + $this->totalCashGameProfit() + $this->totalTournamentProfit();
    }

    /**
    * Returns the sum of all bankroll transactions
    *
    * @return int
    */
    public function totalBankrollTransactionAmounts()
    {
        $total = $this->bankrollTransactions->reduce(function ($total, $bankrollTransaction) {
            return $total + $bankrollTransaction->locale_amount;
        }, 0);

        return $total;
    }

    /**
    * Returns the sum of all cash game profits in locale currency
    *
    * @return int
    */
    public function totalCashGameProfit()
    {
        $total = $this->cashGames->reduce(function ($total, $cashGame) {
            return $total + $cashGame->localeProfit;
        }, 0);

        return $total;
    }

    /**
    * Returns the sum of all tournament profits
    *
    * @return int
    */
    public function totalTournamentProfit()
    {
        $total = $this->tournaments->reduce(function ($total, $tournament) {
            return $total + $tournament->localeProfit;
        }, 0);

        return $total;
    }

    /**
    * Returns the default stakes and any custom user stakes
    *
    * @return Collection
    */
    public function getStakesAttribute()
    {
        return Stake::where('user_id', $this->id)
                        ->orWhere('user_id', null)
                        ->orderBy('small_blind')
                        ->orderBy('big_blind')
                        ->get();
    }
}
