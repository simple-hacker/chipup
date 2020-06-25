<?php

namespace App\Http\Controllers;

use App\CashGame;
use Illuminate\Support\Carbon;
use App\Http\Requests\CreateCashGameRequest;
use App\Http\Requests\UpdateCashGameRequest;

class CashGameController extends GameController
{
    /**
    * GET method to retrieve auth user's cash games
    *
    * @return json
    */
    public function index()
    {
        return response()->json([
            'success' => true,
            'cash_games' => auth()->user()->cashGames()->whereNotNull('end_time')->get()
        ]);
    }

    /**
    * GET method to retrieve specific cash game
    *
    * @param CashGame $cashGame
    * @return json
    */
    public function view(CashGame $cashGame)
    {
        $this->authorize('manage', $cashGame);

        return response()->json([
            'success' => true,
            'cash_game' => $cashGame
        ]);
    }

    /**
    * POST method to create a completed cash game with all required attributes.
    * 
    * @param CreateCashGameRequest $request
    * @return json
    */
    public function create(CreateCashGameRequest $request)
    {
        try {
            $this->checkIfRequestTimesClashWithAnotherCashGame();
            $this->checkIfCashGameRequestHasBuyIn();

            // Create the Cash Game with details (without transaction) from the request
            $cashGameAttributes = $this->removeTransactionsFromRequest($request->validated());
            $cashGame = auth()->user()->cashGames()->create($cashGameAttributes);
            
            // Add the BuyIns.
            // Amount is required in request, but using null coalescing to zero just in case
            foreach ($request->buy_ins as $buy_in) {
                $cashGame->addBuyIn($buy_in['amount'] ?? 0, $buy_in['currency'] ?? auth()->user()->currency);
            }

            $this->createExpensesFromRequest($cashGame);
            $this->createCashOutFromRequest($cashGame);

            return [
                'success' => true,
                'cash_game' => $cashGame->fresh()
            ];
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    /**
    * PATCH method to update cash game.
    * 
    * @param CashGame $cashGame
    * @param UpdateCashGameRequest $request
    * @return json
    */
    public function update(CashGame $cashGame, UpdateCashGameRequest $request)
    {
        $this->authorize('manage', $cashGame);

        try {
            $this->checkIfUpdateRequestTimesAreValidAgainstSavedTimes($cashGame);
            $this->checkIfRequestTimesClashWithAnotherCashGame($cashGame->id);
    
            // Update the cash game with the validated request
            $cashGame->update($request->validated());
    
            return response()->json([
                'success' => true,
                'cash_game' => $cashGame->fresh()
            ]);

        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    /**
    * DELETE method to delete specific cash game
    *
    * @param CashGame $cashGame
    * @return json
    */
    public function destroy(CashGame $cashGame)
    {
        $this->authorize('manage', $cashGame);

        $cashGame->delete();
        
        return response()->json([
            'success' => true,
        ]);
    }
}
