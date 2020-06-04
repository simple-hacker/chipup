<?php

namespace App\Http\Controllers;

use App\CashGame;
use App\Tournament;
use App\Abstracts\GameTransaction;
use App\Exceptions\InvalidGameTypeException;
use App\Http\Requests\CreateGameTransactionRequest;
use App\Http\Requests\UpdateGameTransactionRequest;

abstract class GameTransactionController extends Controller
{
    protected $transaction_type;
    protected $transaction_relationship;

    /**
    * Create a transaction to the CashGame
    * 
    * @param CreateGameTransactionRequest $request
    * @return json
    */
    public function create(CreateGameTransactionRequest $request)
    {
        // Get the model for the correct GameType
        switch ($request->game_type) {
            case 'cash_game':
                $game = CashGame::findOrFail($request->game_id);
                break;
            case 'tournament':
                $game = Tournament::findOrFail($request->game_id);
                break;
            default:
                throw new InvalidGameTypeException();
        }

        // Authorize that the user can manage the game
        $this->authorize('manage', $game);

        try {
            $game_transaction = $game->addTransaction($this->transaction_type, $request->amount);

            // Add Comments
            if ($request->comments) {
                $game_transaction->update(['comments' => $request->comments]);
            }

            return response()->json([
                'success' => true,
                'transaction' => $game_transaction,
                'game' => $game->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
    * Retrieve the Transaction
    * Make sure it belongs to the correct game_type
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
            'transaction' => $game_transaction,
            'game' => $game_transaction->game->fresh(),
        ]);
    }

    /**
    * Destroy the Transaction
    * 
    * @param GameTransaction $game_transaction
    * @return json
    */
    public function destroy(GameTransaction $game_transaction)
    {
        $this->authorize('manage', $game_transaction);
        
        $success = $game_transaction->delete();

        return response()->json([
            'success' => $success,
            'game' => $game_transaction->game->fresh(),
        ]);
    }
}
