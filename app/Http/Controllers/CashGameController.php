<?php

namespace App\Http\Controllers;

use App\CashGame;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Requests\AddCashGameRequest;
use App\Http\Requests\UpdateCashGameRequest;

class CashGameController extends Controller
{
    /**
    * POST method for starting a Cash Game for the authenticated user
    * 
    * @param AddCashGameRequest $request
    * @return json 
    */
    public function start(AddCashGameRequest $request)
    {
        try {
            $cash_game = auth()->user()->startCashGame($request->validated());

            if ($request->amount) {
                $cash_game->addBuyIn($request->amount);
            }

            return [
                'success' => true
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
            ], 422);
        }
    }

    /**
    * POST method to end the current live Cash Game
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

        // Get the current live Cash Game if there is one.
        $cash_game = auth()->user()->liveCashGame();

        if ($cash_game) {
            // If there is a live CashGame try to end if with supplied time or null
            try {
                $end_time = ($request->end_time) ? Carbon::create($request->end_time) : null;
                $cash_game->end($end_time);
                if ($request->amount) {
                    $cash_game->cashOut($request->amount);
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
                'message' => 'You currently don\'t have a Cash Game in progress'
            ], 422);
        }
    }

    /**
    * GET method to retrieve auth user's cash games
    *
    * @return json
    */
    public function index()
    {
        return response()->json([
            'success' => true,
            'cash_games' => auth()->user()->cashGames()->get()
        ]);
    }

    /**
    * GET method to retrieve specific cash game
    *
    * @param CashGame $cash_game
    * @return json
    */
    public function view(CashGame $cash_game)
    {
        $this->authorize('manage', $cash_game);

        return response()->json([
            'success' => true,
            'cash_game' => $cash_game
        ]);
    }

    /**
    * PATCH method to retrieve specific cash game
    *
    * @param CashGame $cash_game
    * @param UpdateCashGameRequest $request
    * @return json
    */
    public function update(CashGame $cash_game, UpdateCashGameRequest $request)
    {
        $this->authorize('manage', $cash_game);

        $cash_game->update($request->validated());
        
        return response()->json([
            'success' => true,
            'cash_game' => $cash_game
        ]);
    }

    /**
    * PATCH method to retrieve specific cash game
    *
    * @param CashGame $cash_game
    * @return json
    */
    public function destroy(CashGame $cash_game)
    {
        $this->authorize('manage', $cash_game);

        $cash_game->delete();
        
        return response()->json([
            'success' => true,
        ]);
    }
}
