<?php

namespace App\Http\Controllers;

use App\CashGame;
use App\Transactions\BuyIn;
use App\Transactions\Expense;
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
    * POST method to create a completed cash game with all required attributes.
    * 
    * @param CreateCashGameRequest $request
    * @return json
    */
    public function create(CreateCashGameRequest $request)
    {
        try {
            // If start_time conflicts with another cash game then reject.
            if (auth()->user()->cashGamesAtTime(Carbon::create($request->start_time)) > 0) {
                throw new \Exception('You already have another cash game at that time.', 422);
            }
            
            // If no buyins were provided then reject.
            if (!$request->buy_ins) {
                throw new \Exception('At least one buy in must be supplied', 422);
            }
            
            $cash_game = auth()->user()->cashGames()->create(
                $request->except(['buy_ins', 'expenses', 'cash_out'])
            );
            
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
            $cash_out = $request->cash_out['amount'] ?? 0;
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
    * PATCH method to update cash game.
    * 
    * @param CashGame $cash_game
    * @param UpdateCashGameRequest $request
    * @return json
    */
    public function update(CashGame $cash_game, UpdateCashGameRequest $request)
    {
        $this->authorize('manage', $cash_game);

        try {
            // Update Cash Game attributes if provided.
            $cash_game->update($request->except(['buy_ins', 'expenses', 'cash_out']));

            // Check Buy Ins
            if ($request->buy_ins) {
                foreach ($request->input('buy_ins') as $buy_in) {
                    // Find BuyIn via id and update amount, or create a new BuyIn transaction via it's CashGame.
                    $id = $buy_in['id'] ?? null;

                    // If Buy In id is provided, locate, authorize and update.
                    if ($id) {
                        // Find buy in
                        $buy_in_transaction = BuyIn::find($id);
                        // Check it belongs to cash game
                        if ($buy_in_transaction->game->is($cash_game)) {
                            // Update if Buy In Transactions belongs to Cash Game
                            $buy_in_transaction->update(['amount' => $buy_in['amount']]);
                        } else {
                            // Buy In Transaction does not belong to Cash Game so Forbidden to update.
                            throw new \Exception('You do not have permission to update this Buy In transaction', 403);
                        }
                    }
                    // Else just create a new Buy In through Cash Game.
                    else {
                        $cash_game->buyIns()->create(['amount' => $buy_in['amount']]);
                    }
                }
            }

            // Check Expenses
            if ($request->expenses) {
                foreach ($request->input('expenses') as $expense) {
                    // Find Expense via id and update amount, or create a new Expense transaction via it's CashGame.
                    $id = $expense['id'] ?? null;

                    // If Expense id is provided, locate, authorize and update.
                    if ($id) {
                        // Find buy in
                        $expense_transaction = Expense::find($id);
                        // Check it belongs to cash game
                        if ($expense_transaction->game->is($cash_game)) {
                            // Update if Expense Transactions belongs to Cash Game
                            $expense_transaction->update([
                                'amount' => $expense['amount'],
                                'comments' => $expense['comments'] ?? null
                            ]);
                        } else {
                            // Expense Transaction does not belong to Cash Game so Forbidden to update.
                            throw new \Exception('You do not have permission to update this Expense transaction', 403);
                        }
                    }
                    // Else just create a new Buy In through Cash Game.
                    else {
                        $cash_game->expenses()->create([
                            'amount' => $expense['amount'],
                            'comments' => $expense['comments'] ?? null
                        ]);
                    }
                }
            }

            // Check Cash Out
            // Need to check if set because sending through ['cash_out' => []] will result in 0 amount.
            // If no changes are to be made then cash_out array will not be set so it won't get updated to 0.
            if (isset($request->cash_out)) {
                $cash_game->cashOutModel->updateOrCreate([], [
                    'amount' => $request->cash_out['amount'] ?? 0
                ]);
            }

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
