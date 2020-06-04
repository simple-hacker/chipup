<?php

namespace App\Http\Controllers;

use App\Http\Requests\EndSessionRequest;
use App\Http\Requests\StartTournamentRequest;
use App\Http\Requests\UpdateLiveTournamentRequest;

class LiveTournamentController extends GameController
{
/**
    * POST method for starting a Tournament for the authenticated user
    * 
    * @param StartTournamentRequest $request
    * @return json 
    */
    public function start(StartTournamentRequest $request)
    {
        try {
            $tournament = auth()->user()->startTournament($request->validated());

            // If no BuyIn is provided still create a transaction with amount zero
            // This is because Freeroll tournaments are possible.
            $tournament->addBuyIn($request->amount ?? 0);

            return [
                'success' => true,
                'tournament' => $tournament
            ];
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    /**
    * Returns the User's current Tournament or null
    * 
    * @return json
    */
    public function current()
    {
        try {
            $tournament = auth()->user()->liveTournament() ?? $this->throwLiveTournamentNotStartedException();

            return response()->json([
                'success' => true,
                'status' => 'live',
                'tournament' => $tournament
            ]);

        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    /**
    * POST method to end the current live Tournament
    * 
    * @param EndSessionRequest $request
    * @return json
    */
    public function end(EndSessionRequest $request)
    {
        try {
            $tournament = auth()->user()->liveTournament() ?? $this->throwLiveTournamentNotStartedException();

            $tournament->endAndCashOut($request->end_time, $request->amount ?? 0);

            return response()->json([
                'success' => true,
                'status' => 'live',
                'tournament' => $tournament
            ]);

        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    /**
    * PATCH method to update specific cash game
    *
    * @param Tournament $tournament
    * @param UpdateTournamentRequest $request
    * @return json
    */
    public function update(UpdateLiveTournamentRequest $request)
    {
        try {
            $tournament = auth()->user()->liveTournament() ?? $this->throwLiveTournamentNotStartedException();

            $this->checkIfRequestTimesClashWithAnotherTournament();

            $tournament->update($request->validated());

            return response()->json([
                'success' => true,
                'tournament' => $tournament->fresh()
            ]);

        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }
}
