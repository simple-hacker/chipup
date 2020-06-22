<?php

namespace App\Http\Controllers;

use App\Http\Requests\StartTournamentRequest;
use App\Http\Requests\UpdateLiveTournamentRequest;

class LiveTournamentController extends LiveGameController
{
    /**
    * Set variables for parent abstract controller
    * 
    */
    public function __construct()
    {
        $this->game_type = 'tournament';
    }

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
            $tournament->addBuyIn($request->amount ?? 0, $request->currency ?? auth()->user()->currency);

            return [
                'success' => true,
                'game' => $tournament->fresh(),
            ];
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
            $tournament = auth()->user()->liveTournament();

            if (!$tournament) {
                $this->throwLiveSessionNotStartedException();
            }

            $this->checkIfRequestTimesClashWithAnotherTournament($tournament->id);

            $tournament->update($request->validated());

            return response()->json([
                'success' => true,
                'game' => $tournament->fresh()
            ]);

        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }
}
