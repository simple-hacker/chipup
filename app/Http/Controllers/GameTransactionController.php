<?php

namespace App\Http\Controllers;

use App\Abstracts\Game;
use App\Abstracts\GameTransaction;
use App\Http\Requests\GameTransactionRequest;

abstract class GameTransactionController extends Controller
{
    protected $transaction_type;
    protected $transaction_relationship;

    /**
    * Add a buy in to the CashGame
    * 
    * @param GameTransactionRequest $request
    * @return json
    */
    public function add(Game $game_type, GameTransactionRequest $request)
    {
        $this->authorize('manage', $game_type);

        $game_transaction = $game_type->addTransaction($this->transaction_type, $request->amount);
    
        return response()->json([
            'success' => true,
            'transaction' => $game_transaction
        ]);
    }

    /**
    * Retrieve the Buy In
    * Make sure it belongs to the correct cash_game
    * 
    * @param Game $game_type
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
    * @param Game $game_type
    * @param GameTransaction $game_transaction
    * @param GameTransactionRequest $request
    * @return json
    */
    public function update(GameTransaction $game_transaction, GameTransactionRequest $request)
    {
        $this->authorize('manage', $game_transaction);
        
        $game_transaction->amount = $request->amount;

        if ($request->comments) {
            $game_transaction->comments = $request->comments;
        }

        $game_transaction->save();

        return response()->json([
            'success' => true,
            'transaction' => $game_transaction
        ]);
    }

    /**
    * Destroy the Buy In
    * 
    * @param Game $game_type
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
