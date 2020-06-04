<?php

namespace App\Http\Controllers;

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

            // request->amount is required but set to 0 in case something goes wrong
            $cash_game->addBuyIn($request->amount ?? 0);

            return [
                'success' => true,
                'cash_game' => $cash_game->fresh()
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
        try {
            $cashGame = auth()->user()->liveCashGame();

            if ($cashGame) {
                return response()->json([
                    'success' => true,
                    'status' => 'live',
                    'cash_game' => $cashGame
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

    /**
    * POST method to end the current live Cash Game
    * 
    * @param EndSessionRequest $request
    * @return json
    */
    public function end(EndSessionRequest $request)
    {
        try {
            $cashGame = auth()->user()->liveCashGame();

            if ($cashGame) {

                $cashGame->endAndCashOut($request->end_time, $request->amount ?? 0);

                return response()->json([
                    'success' => true,
                    'status' => 'live',
                    'cash_game' => $cashGame
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

                // If start_time was provided, check it doesn't clash with an exisiting cash game.
                if ($request->start_time && auth()->user()->cashGamesAtTime($request->start_time) > 0) {
                    throw new \Exception('You already have another cash game at that time.', 422);
                }
                
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
