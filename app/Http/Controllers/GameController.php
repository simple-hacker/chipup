<?php

namespace App\Http\Controllers;

use App\Exceptions\ClashingSessionsException;
use Illuminate\Support\Carbon;
use App\Exceptions\InvalidDateException;
use App\Exceptions\SessionNotStartedException;

class GameController extends Controller
{
    /**
    * Able to add transactions along with details in one request when creating a completed tournament or cash game
    * This strips all the transactions from the request so only the game's details remain
    * So not providing invalid data when we create a cash game or tournament through the user
    * i.e. auth()->user()->cashGames()->create($validatedRequest)
    * 
    * @param Array $validatedRequest
    * @return Array
    */
    public function removeTransactionsFromRequest($validatedRequest) : Array
    {
        unset($validatedRequest['buy_ins'], $validatedRequest['buy_in'], $validatedRequest['cash_out'], $validatedRequest['expenses'], $validatedRequest['rebuys'], $validatedRequest['add_ons']);
        return $validatedRequest;
    }

    /**
    * If the request has expenses, add them to the provided game
    * 
    * @param Game $game
    * @return 
    */
    public function createExpensesFromRequest($game)
    {
        // Add the Expenses.
        if (request()->expenses) {
            foreach (request()->expenses as $expense) {
                // Default to 0 if no amount is supplied.
                $game->addExpense($expense['amount'] ?? 0, $expense['comments'] ?? null);
            }
        }
    }

    /**
    * If the request has rebuys, add them to the provided game
    * 
    * @param Game $game
    * @return 
    */
    public function createRebuysFromRequest($game)
    {
        // Add the Rebuys.
        if (request()->rebuys) {
            foreach (request()->rebuys as $rebuys) {
                // Default to 0 if no amount is supplied.
                $game->addRebuy($rebuys['amount'] ?? 0);
            }
        }
    }

    /**
    * If the request has add ons, add them to the provided game
    * 
    * @param Game $game
    * @return 
    */
    public function createAddOnsFromRequest($game)
    {
        // Add the Add On.
        if (request()->add_ons) {
            foreach (request()->add_ons as $add_ons) {
                // Default to 0 if no amount is supplied.
                $game->addAddOn($add_ons['amount'] ?? 0);
            }
        }
    }

    /**
    * If the request has cash out, add it to the game
    * If not, then default to to zero amount
    * 
    * @param Game $game
    * @return 
    */
    public function createCashOutFromRequest($game)
    {
        $cash_out = request()->cash_out['amount'] ?? 0;
        $game->addCashOut($cash_out);
    }

    /**
    * If liveCashGame() returns null/empty, then fire this exception.
    * 
    * @return void
    */
    public function throwLiveCashGameNotStartedException()
    {
        throw new SessionNotStartedException('You have not started a Cash Game.');
    }

    /**
    * If liveTournament() returns null/empty, then fire this exception.
    * 
    * @return void
    */
    public function throwLiveTournamentNotStartedException()
    {
        throw new SessionNotStartedException('You have not started a Tournament.');
    }

    /**
    * Throw an error if cash game request start time clashes with another cash game.
    * 
    * @return void
    */
    public function checkIfRequestTimesClashWithAnotherCashGame($cashGameId = null)
    {
        // If start_time was provided, check it doesn't clash with an exisiting cash game.
        if (request()->start_time && auth()->user()->cashGamesAtTime(request()->start_time, $cashGameId) > 0) {
            throw new ClashingSessionsException('You already have another cash game at that time.');
        }
    }

    /**
    * Throw an error if tournament request start time clashes with another tournament.
    * 
    * @return void
    */
    public function checkIfRequestTimesClashWithAnotherTournament($tournamentId = null)
    {
        // If start_time was provided, check it doesn't clash with an exisiting tournament.
        if (request()->start_time && auth()->user()->tournamentsAtTime(request()->start_time, $tournamentId) > 0) {
            throw new ClashingSessionsException('You already have another tournament at that time');
        }
    }

    /**
    * When providing only start time or end time for updating Game.
    * start time must not be after it's own end time
    * or
    * end time must not be before it's own start time.
    * 
    * @param Game $game
    * @return void
    */
    public function checkIfUpdateRequestTimesAreValidAgainstSavedTimes($game)
    {
        // If only start time was provided, make sure it's after the saved end time.
        if (request()->start_time && !request()->end_time) {
            if (Carbon::create(request()->start_time) > $game->end_time) {
                throw new InvalidDateException('Cannot set the start time after the start time', 422);
            }
        }

        // If only end time is provided, make sure it's before the saved start time
        if (request()->end_time && !request()->start_time) {
            if (Carbon::create(request()->end_time) < $game->start_time) {
                throw new InvalidDateException('Cannot set the end time before the start time', 422);
            }
        }
    }

    /**
    * Throw an error if cash game request does not have Buy In
    * 
    * @return void
    */
    public function checkIfCashGameRequestHasBuyIn()
    {
        if (!request()->buy_ins) {
            throw new \Exception('At least one buy in must be supplied', 422);
        }
    }
}
