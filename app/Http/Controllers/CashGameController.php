<?php

namespace App\Http\Controllers;

use App\CashGame;
use Illuminate\Support\Carbon;
use App\Http\Requests\CreateCashGameRequest;
use App\Http\Requests\UpdateCashGameRequest;

class CashGameController extends Controller
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
    * POST method to create a completed cash game with all required attributes.
    * 
    * @param CreateCashGameRequest $request
    * @return json
    */
    public function create(CreateCashGameRequest $request)
    {
        try {
            // If start_time conflicts with another cash game then reject.
            if (auth()->user()->cashGamesAtTime($request->start_time) > 0) {
                throw new \Exception('You already have another cash game at that time.', 422);
            }
            
            // If no buyins were provided then reject.
            if (!$request->buy_ins) {
                throw new \Exception('At least one buy in must be supplied', 422);
            }

            $cashGameAttributes = $request->validated();
            unset($cashGameAttributes['buy_ins'], $cashGameAttributes['cash_out_model'], $cashGameAttributes['expenses']);

            $cash_game = auth()->user()->cashGames()->create($cashGameAttributes);
            
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
            $cash_out = $request->cash_out_model['amount'] ?? 0;
            $cash_game->cashOut($cash_out);

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
    * PATCH method to update cash game.
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
            'cash_game' => $cash_game->fresh()
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
