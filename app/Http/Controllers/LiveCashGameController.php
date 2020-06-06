<?php

namespace App\Http\Controllers;

use App\Http\Requests\EndSessionRequest;
use App\Http\Requests\StartCashGameRequest;
use App\Http\Requests\UpdateCashGameRequest;
use App\Http\Requests\UpdateLiveCashGameRequest;

class LiveCashGameController extends GameController
{
/**
    * POST method for starting a Cash Game for the authenticated user
    * 
    * @param StartCashGameRequest $request
    * @return json 
    */
    public function start(StartCashGameRequest $request)
    {
        try {
            $cash_game = auth()->user()->startCashGame($request->validated());

            // Amount is required in request, but using null coalescing to zero just in case
            $cash_game->addBuyIn($request->amount ?? 0);

            return [
                'success' => true,
                'cash_game' => $cash_game->fresh()
            ];
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    /**
    * Returns the User's current CashGame or null
    * 
    * @return json
    */
    public function current()
    {
        try {
            $cashGame = auth()->user()->liveCashGame() ?? [];

            return response()->json([
                'success' => true,
                'status' => 'live',
                'cash_game' => $cashGame
            ]);

        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    /**
    * POST method to end the current live Cash Game
    * 
* @param EndSessionRequest $request
    * @return json
    */
    public function end(EndSessionRequest $request)
    {
        try {
            $cash_game = auth()->user()->liveCashGame() ?? $this->throwLiveCashGameNotStartedException();

            $cash_game->endAndCashOut($request->end_time, $request->amount ?? 0);

            return response()->json([
                'success' => true,
                'status' => 'live',
                'cash_game' => $cash_game->fresh()
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
    * @param CashGame $cashGame
    * @param UpdateCashGameRequest $request
    * @return json
    */
    public function update(UpdateLiveCashGameRequest $request)
    {
        try {
            $cashGame = auth()->user()->liveCashGame() ?? $this->throwLiveCashGameNotStartedException();

            $this->checkIfRequestTimesClashWithAnotherCashGame();
            
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
}
