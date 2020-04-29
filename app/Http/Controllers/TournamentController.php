<?php

namespace App\Http\Controllers;

use App\Tournament;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Requests\AddTournamentRequest;
use App\Http\Requests\UpdateTournamentRequest;

class TournamentController extends Controller
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
                $end_time = ($request->end_time) ? Carbon::create($request->end_time) : null;
                $tournament->end($end_time);
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

    /**
    * GET method to retrieve auth user's tournaments
    *
    * @return json
    */
    public function index()
    {
        return response()->json([
            'success' => true,
            'tournaments' => auth()->user()->tournaments()->get()
        ]);
    }

    /**
    * GET method to retrieve specific tournament
    *
    * @param Tournament $tournament
    * @return json
    */
    public function view(Tournament $tournament)
    {
        $this->authorize('manage', $tournament);

        return response()->json([
            'success' => true,
            'tournament' => $tournament
        ]);
    }

    /**
    * PATCH method to retrieve specific tournament
    *
    * @param Tournament $tournament
    * @param UpdateTournamentRequest $request
    * @return json
    */
    public function update(Tournament $tournament, UpdateTournamentRequest $request)
    {
        $this->authorize('manage', $tournament);

        $tournament->update($request->validated());
        
        return response()->json([
            'success' => true,
            'tournament' => $tournament
        ]);
    }

    /**
    * DELETE method to retrieve specific tournament
    *
    * @param Tournament $tournament
    * @return json
    */
    public function destroy(Tournament $tournament)
    {
        $this->authorize('manage', $tournament);

        $tournament->delete();
        
        return response()->json([
            'success' => true,
        ]);
    }
}
