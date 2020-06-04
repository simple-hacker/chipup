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
            $tournament = auth()->user()->liveTournament();

            if ($tournament) {
                return response()->json([
                    'success' => true,
                    'status' => 'live',
                    'tournament' => $tournament
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

    /**
    * POST method to end the current live Tournament
    * 
    * @param EndSessionRequest $request
    * @return json
    */
    public function end(EndSessionRequest $request)
    {
        try {
            $tournament = auth()->user()->liveTournament();

            if ($tournament) {

                $tournament->endAndCashOut($request->end_time, $request->amount ?? 0);

                return response()->json([
                    'success' => true,
                    'status' => 'live',
                    'tournament' => $tournament
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

                // If start_time was provided, check it doesn't clash with an exisiting tournament.
                if ($request->start_time && auth()->user()->tournamentsAtTime($request->start_time) > 0) {
                    throw new \Exception('You already have another tournament at that time.', 422);
                }

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
