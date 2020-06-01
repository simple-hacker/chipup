<?php

namespace App\Http\Controllers;

use App\Tournament;
use App\Http\Requests\UpdateTournamentRequest;

class TournamentController extends Controller
{
    /**
    * GET method to retrieve auth user's tournaments
    *
    * @return json
    */
    public function index()
    {
        return response()->json([
            'success' => true,
            'tournaments' => auth()->user()->tournaments()->whereNotNull('end_time')->get()
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
            'tournament' => $tournament->fresh()
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
