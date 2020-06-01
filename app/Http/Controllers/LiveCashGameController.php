<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Requests\EndSessionRequest;
use App\Http\Requests\StartCashGameRequest;
use App\Http\Requests\UpdateCashGameRequest;
use App\Http\Requests\UpdateLiveCashGameRequest;

class LiveCashGameController extends Controller
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

            // request->amount is required
            $cash_game->addBuyIn($request->amount);

            return [
                'success' => true,
                'cash_game' => $cash_game->fresh() // NOTE: Returning fresh copy of cash_game because of transactions.
            ];
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
    * Returns the User's current CashGame or null
    * 
    * @return json
    */
    public function current()
    {
        $cash_game = auth()->user()->liveCashGame();

        if ($cash_game) {
            return response()->json([
                'success' => true,
                'status' => 'live',
                'cash_game' => $cash_game
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'You currently don\'t have a Cash Game in progress'
            ]);
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
        // Get the current live Cash Game if there is one.
        $cash_game = auth()->user()->liveCashGame();

        if ($cash_game) {
            // If there is a live CashGame try to end if with supplied time or null
            try {
                $cash_game->end($request->end_time);
                $cash_game->cashOut($request->amount);

                return response()->json([
                    'success' => true,
                    'cash_game' => $cash_game->fresh()
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], $e->getCode());
            }
        } else {
            // Else send a 422
            return response()->json([
                'success' => false,
                'message' => 'You currently don\'t have a Cash Game in progress'
            ], 422);
        }
    }

    /**
    * PATCH method to update specific cash game
    *
    * @param CashGame $cash_game
    * @param UpdateCashGameRequest $request
    * @return json
    */
    public function update(UpdateLiveCashGameRequest $request)
    {
        try {
            $cash_game = auth()->user()->liveCashGame();

            if ($cash_game) {
                
                $cash_game->update($request->validated());

                return response()->json([
                    'success' => true,
                    'cash_game' => $cash_game->fresh()
                ]);
            } else {
                throw new \Exception('You have not started a Cash Game.', 422);
            }
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }
}
