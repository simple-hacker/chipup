<?php

namespace App\Http\Controllers;

use App\Http\Requests\StartCashGameRequest;
use App\Http\Requests\UpdateLiveCashGameRequest;

class LiveCashGameController extends LiveGameController
{
    /**
    * Set variables for parent abstract controller
    * 
    */
    public function __construct()
    {
        $this->game_type = 'cash_out';
    }

    /**
    * POST method for starting a Cash Game for the authenticated user
    * 
    * @param StartCashGameRequest $request
    * @return json 
    */
    public function start(StartCashGameRequest $request)
    {
        try {
            $cashGame = auth()->user()->startCashGame($request->validated());

            // Amount is required in request, but using null coalescing to zero just in case
            $cashGame->addBuyIn($request->amount ?? 0, $request->currency ?? auth()->user()->currency);

            return [
                'success' => true,
                'game' => $cashGame->fresh()
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
    * @param CashGame $cashGame
    * @param UpdateLiveCashGameRequest $request
    * @return json
    */
    public function update(UpdateLiveCashGameRequest $request)
    {
        try {
            $cashGame = auth()->user()->liveCashGame();

            if (!$cashGame) {
                $this->throwLiveSessionNotStartedException();
            }

            $this->checkIfRequestTimesClashWithAnotherCashGame($cashGame->id);
            
            $cashGame->update($request->validated());

            return response()->json([
                'success' => true,
                'game' => $cashGame->fresh()
            ]);
            
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }
}
