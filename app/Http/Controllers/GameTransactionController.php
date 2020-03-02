<?php

namespace App\Http\Controllers;

use App\CashGame;
use App\Tournament;
use App\Abstracts\GameTransaction;
use App\Http\Requests\AddGameTransactionRequest;
use App\Http\Requests\UpdateGameTransactionRequest;

abstract class GameTransactionController extends Controller
{
    protected $transaction_type;
    protected $transaction_relationship;

    /**
    * Add a buy in to the CashGame
    * 
    * @param AddGameTransactionRequest $request
    * @return json
    */
    public function add(AddGameTransactionRequest $request)
    {
        // Get the model for the correct GameType
        switch ($request->game_type) {
            case 'cashgame':
                $game_type = CashGame::findOrFail($request->id);
                break;
            case 'tournament':
                $game_type = Tournament::findOrFail($request->id);
                break;
            default:
                abort(422, 'Invalid GameType was supplied');
        }

        // Authorize that the user can manage the game_type
        $this->authorize('manage', $game_type);

        try {
            $game_transaction = $game_type->addTransaction($this->transaction_type, $request->amount);

            // Add Comments
            if ($request->comments) {
                $game_transaction->update(['comments' => $request->comments]);
            }

            return response()->json([
                'success' => true,
                'transaction' => $game_transaction
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
    * Retrieve the Buy In
    * Make sure it belongs to the correct cash_game
    * 
    * @param GameTransaction $game_transaction
    * @return json
    */
    public function view(GameTransaction $game_transaction)
    {
        $this->authorize('manage', $game_transaction);

        return response()->json([
            'success' => true,
            'transaction' => $game_transaction
        ]);
    }

    /**
    * Update the GameTransaction with the GameTransactionRequest amount
    * 
    * @param GameTransaction $game_transaction
    * @param UpdateGameTransactionRequest $request
    * @return json
    */
    public function update(GameTransaction $game_transaction, UpdateGameTransactionRequest $request)
    {
        $this->authorize('manage', $game_transaction);

        // Only updated the validated request data.
        // This is because amount and comments are sometimes present.
        $game_transaction->update($request->validated());

        return response()->json([
            'success' => true,
            'transaction' => $game_transaction
        ]);
    }

    /**
    * Destroy the Buy In
    * 
    * @param GameTransaction $game_transaction
    * @return json
    */
    public function destroy(GameTransaction $game_transaction)
    {
        $this->authorize('manage', $game_transaction);
        
        $success = $game_transaction->delete();

        return response()->json([
            'success' => $success
        ]);
    }
}
