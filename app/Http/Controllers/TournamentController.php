<?php

namespace App\Http\Controllers;

use App\Tournament;
use App\Http\Requests\CreateTournamentRequest;
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
    * POST method to create a completed tournament with all required attributes.
    * 
    * @param CreateTournamentRequest $request
    * @return json
    */
    public function create(CreateTournamentRequest $request)
    {
        try {
            // If start_time conflicts with another tournament then reject.
            if (auth()->user()->tournamentsAtTime($request->start_time) > 0) {
                throw new \Exception('You already have another tournament at that time.', 422);
            }
            
            $tournamentAttributes = $request->validated();
            unset($tournamentAttributes['buy_ins'], $tournamentAttributes['cash_out_model'], $tournamentAttributes['expenses'], $tournamentAttributes['rebuys'], $tournamentAttributes['add_ons']);

            $tournament = auth()->user()->tournaments()->create($tournamentAttributes);
            
            // Add the BuyIn if provided, else create one with 0 for Freeroll
            $tournament->addBuyIn($request->buy_in['amount'] ?? 0);
            
            // Add the Expenses.
            if ($request->expenses) {
                foreach ($request->expenses as $expense) {
                    // Default to 0 if no amount is supplied.
                    $tournament->addExpense($expense['amount'] ?? 0, $expense['comments'] ?? null);
                }
            }

            // Add the Rebuys.
            if ($request->rebuys) {
                foreach ($request->rebuys as $rebuy) {
                    // Default to 0 if no amount is supplied.
                    $tournament->addRebuy($rebuy['amount'] ?? 0);
                }
            }

            // Add the Add Ons.
            if ($request->add_ons) {
                foreach ($request->add_ons as $add_on) {
                    // Default to 0 if no amount is supplied.
                    $tournament->addAddOn($add_on['amount'] ?? 0);
                }
            }
            
            // CashOut the CashGame straight away with CashOut amount and end_time
            // If no cashout time or amount is supplied then it defaults to Now() and 0
            $cash_out = $request->cash_out_model['amount'] ?? 0;
            $tournament->cashOut($cash_out);

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
