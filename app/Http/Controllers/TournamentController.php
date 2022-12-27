<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use Illuminate\Support\Carbon;
use App\Http\Requests\CreateTournamentRequest;
use App\Http\Requests\UpdateTournamentRequest;

class TournamentController extends GameController
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
    * POST method to create a completed tournament with all required attributes.
    *
    * @param CreateTournamentRequest $request
    * @return json
    */
    public function create(CreateTournamentRequest $request)
    {
        try {
            $this->checkIfRequestTimesClashWithAnotherTournament();

            // Create the Tournament with details (without transaction) from the request
            $tournamentAttributes = $this->removeTransactionsFromRequest($request->validated());
            $tournament = auth()->user()->tournaments()->create($tournamentAttributes);

            // Add the BuyIn if provided, else create one with 0 for Freeroll
            $tournament->addBuyIn($request->buy_in['amount'] ?? 0, $request->buy_in['currency'] ?? auth()->user()->currency);

            $this->createExpensesFromRequest($tournament);
            $this->createRebuysFromRequest($tournament);
            $this->createAddOnsFromRequest($tournament);
            $this->createCashOutFromRequest($tournament);

            return [
                'success' => true,
                'tournament' => $tournament->fresh()
            ];
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
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

        try {
            $this->checkIfUpdateRequestTimesAreValidAgainstSavedTimes($tournament);
            $this->checkIfRequestTimesClashWithAnotherTournament($tournament->id);

            // Update the tournament with the validated request
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
