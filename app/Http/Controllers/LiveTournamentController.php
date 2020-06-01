<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Requests\AddTournamentRequest;

class LiveTournamentController extends Controller
{
/**
    * POST method for starting a Tournament for the authenticated user
    * 
    * @param AddTournamentRequest $request
    * @return json 
    */
    public function start(AddTournamentRequest $request)
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
    * @param Request $request
    * @return json
    */
    public function end(Request $request)
    {
        $request->validate([
            'end_time' => 'nullable|date',
            'amount' => 'sometimes|integer|min:0'
        ]);

        // Get the current live Tournament if there is one.
        $tournament = auth()->user()->liveTournament();

        if ($tournament) {
            // If there is a live Tournament try to end if with supplied time or null
            try {
                $tournament->end($request->end_time);
                if ($request->amount) {
                    $tournament->cashOut($request->amount);
                }
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
}
