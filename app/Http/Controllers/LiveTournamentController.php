<?php

namespace App\Http\Controllers;

use App\Http\Requests\EndSessionRequest;
use App\Http\Requests\StartTournamentRequest;
use App\Http\Requests\UpdateLiveTournamentRequest;

class LiveTournamentController extends Controller
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

            if ($request->amount) {
                $tournament->addBuyIn($request->amount);
            }

            return [
                'success' => true,
                'tournament' => $tournament
            ];
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
    * Returns the User's current Tournament or null
    * 
    * @return json
    */
    public function current()
    {
        $tournament = auth()->user()->liveTournament();

        if ($tournament) {
            return response()->json([
                'success' => true,
                'status' => 'live',
                'tournament' => $tournament
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'You currently don\'t have a Tournament in progress'
            ], 422);
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
        // Get the current live Tournament if there is one.
        $tournament = auth()->user()->liveTournament();

        if ($tournament) {
            // If there is a live Tournament try to end if with supplied time or null
            try {
                $tournament->endAndCashOut($request->end_time, $request->amount);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
        } else {
            // Else send a 422
            return response()->json([
                'success' => false,
                'message' => 'You currently don\'t have a Tournament in progress'
            ], 422);
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

            if ($tournament) {
                
                $tournament->update($request->validated());

                return response()->json([
                    'success' => true,
                    'tournament' => $tournament->fresh()
                ]);
            } else {
                throw new \Exception('You have not started a Tournament.', 422);
            }
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }
}
