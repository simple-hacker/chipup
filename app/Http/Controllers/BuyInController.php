<?php

namespace App\Http\Controllers;

use App\Abstracts\Game;
use App\Http\Requests\GameTransactionRequest;
use App\Transactions\BuyIn;

class BuyInController extends Controller
{










    // NOTE: Need to validate that BuyIn belongs to GameType




























    /**
    * Add a buy in to the CashGame NOTE:  This needs to change to be polymorphic with both GameType and GameTransactionType
    * 
    * @param GameTransactionRequest $request
    * @return json
    */
    public function add(Game $game_type, GameTransactionRequest $request)
    {
        $this->authorize('manage', $game_type);
        
        $game_type->addBuyIn($request->amount);
    
        return response()->json([
            'success' => true
        ]);
    }

    /**
    * Retrieve the Buy In
    * Make sure it belongs to the correct cash_game
    * 
    * @param Game $game_type
    * @param BuyIn $buy_in
    * @return json
    */
    public function view(Game $game_type, BuyIn $buy_in)
    {
        $this->authorize('manage', $game_type);

        if (! $game_type->buyIns->contains($buy_in)) {
            abort(422, 'GameTransaction does not belong to GameType');
        }

        return response()->json([
            'success' => true,
            'buy_in' => $buy_in
        ]);
    }

    /**
    * Update the BuyIn with the GameTransactionRequest amount
    * 
    * @param Game $game_type
    * @param BuyIn $buy_in
    * @param GameTransactionRequest $request
    * @return json
    */
    public function update(Game $game_type, BuyIn $buy_in, GameTransactionRequest $request)
    {
        $this->authorize('manage', $game_type);

        if (! $game_type->buyIns->contains($buy_in)) {
            abort(422, 'GameTransaction does not belong to GameType');
        }
        
        $buy_in->amount = $request->amount;

        if ($request->comments) {
            $buy_in->comments = $request->comments;
        }

        $buy_in->save();

        return response()->json([
            'success' => true,
            'buy_in' => $buy_in
        ]);
    }

    /**
    * Destroy the Buy In
    * 
    * @param Game $game_type
    * @param BuyIn $buy_in
    * @return json
    */
    public function destroy(Game $game_type, BuyIn $buy_in)
    {
        $this->authorize('manage', $game_type);

        if (! $game_type->buyIns->contains($buy_in)) {
            abort(422, 'GameTransaction does not belong to GameType');
        }
        
        $success = $buy_in->delete();

        return response()->json([
            'success' => $success
        ]);
    }
}
