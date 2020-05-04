<?php

namespace App\Http\Controllers;

use App\CashGame;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Requests\StartCashGameRequest;
use App\Http\Requests\CreateCashGameRequest;
use App\Http\Requests\UpdateCashGameRequest;

class CashGameController extends Controller
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

            if ($request->amount) {
                $cash_game->addBuyIn($request->amount);
            }

            return [
                'success' => true,
                'cash_game' => $cash_game
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
            'amount' => 'required|numeric|min:0'
        ]);

        // Get the current live Cash Game if there is one.
        $cash_game = auth()->user()->liveCashGame();

        if ($cash_game) {
            // If there is a live CashGame try to end if with supplied time or null
            try {
                $end_time = ($request->end_time) ? Carbon::create($request->end_time) : null;
                $cash_game->cashOut($request->amount, $end_time);

                return response()->json([
                    'success' => true,
                ]);

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
            'cash_games' => auth()->user()->cashGames()->whereNotNull('end_time')->get()
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
            // If start_time conflicts with another cash game then reject.
            if (auth()->user()->cashGamesAtTime(Carbon::create($request->cash_game['start_time'])) > 0) {
                throw new \Exception('You already have another cash game at that time.');
            }

            // If no buyins were provided then reject.
            if (!$request->buy_ins) {
                throw new \Exception('At least one buy in must be supplied');
            }

            $cash_game = auth()->user()->cashGames()->create($request->cash_game);
            
            // Add the BuyIn.
            foreach ($request->buy_ins as $buy_in) {
                $cash_game->addBuyIn($buy_in['amount']);
            }

            // Add the Expenses.
            if ($request->expenses) {
                foreach ($request->expenses as $expense) {
                    // Default to 0 if no amount is supplied.
                    $cash_game->addExpense($expense['amount'] ?? 0, $expense['comments'] ?? null);
                }
            }

            // CashOut the CashGame straight away with CashOut amount and end_time
            // If no cashout time or amount is supplied then it defaults to Now() and 0
            $end_time = isset($request->cash_out['end_time']) ? Carbon::create($request->cash_out['end_time']) : null;
            $cash_out = $request->cash_out['amount'] ?? 0;
            $cash_game->cashOut($cash_out, $end_time);

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
    * PATCH method to update specific cash game
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
    * DELETE method to delete specific cash game
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
