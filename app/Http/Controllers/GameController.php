<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;

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
        unset($validatedRequest['buy_ins'], $validatedRequest['buy_in'], $validatedRequest['cash_out_model'], $validatedRequest['expenses'], $validatedRequest['rebuys'], $validatedRequest['add_ons']);
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
        $cash_out = request()->cash_out_model['amount'] ?? 0;
        $game->cashOut($cash_out);
    }

    /**
    * If liveCashGame() returns null/empty, then fire this exception.
    * 
    * @return void
    */
    public function throwLiveCashGameNotStartedException()
    {
        throw new \Exception('You have not started a Cash Game.', 422);
    }

    /**
    * If liveTournament() returns null/empty, then fire this exception.
    * 
    * @return void
    */
    public function throwLiveTournamentNotStartedException()
    {
        throw new \Exception('You have not started a Tournament.', 422);
    }

    /**
    * Throw an error if cash game request start time clashes with another cash game.
    * 
    * @return void
    */
    public function checkIfRequestTimesClashWithAnotherCashGame()
    {
        // If start_time was provided, check it doesn't clash with an exisiting cash game.
        if (request()->start_time && auth()->user()->cashGamesAtTime(request()->start_time) > 0) {
            throw new \Exception('You already have another cash game at that time.', 422);
        }
    }

    /**
    * Throw an error if tournament request start time clashes with another tournament.
    * 
    * @return void
    */
    public function checkIfRequestTimesClashWithAnotherTournament()
    {
        // If start_time was provided, check it doesn't clash with an exisiting tournament.
        if (request()->start_time && auth()->user()->tournamentsAtTime(request()->start_time) > 0) {
            throw new \Exception('You already have another tournament at that time.', 422);
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
                throw new \Exception('Start time cannot be after end time', 422);
            }
        }

        // If only end time is provided, make sure it's before the saved start time
        if (request()->end_time && !request()->start_time) {
            if (Carbon::create(request()->end_time) < $game->start_time) {
                throw new \Exception('End time cannot be before start time', 422);
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
