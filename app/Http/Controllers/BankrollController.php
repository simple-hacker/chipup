<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transactions\Bankroll;
use App\Http\Requests\BankrollTransactionRequest;


class BankrollController extends Controller
{
    /**
    * POST method to add to the user's bankroll.
    * 
    * @param BankrollTransactionRequest $request
    * @return json
    */
    public function create(BankrollTransactionRequest $request)
    {
        auth()->user()->createBankrollTransaction($request->amount);

        return [
            'success' => true,
        ];
    }

    /**
    * PATCH method to update a Bankroll Transaction
    * 
    * @param Bankroll $bankrollTransaction
    * @param BankrollTransactionRequest $request
    * @return json
    */
    public function update(Bankroll $bankrollTransaction, BankrollTransactionRequest $request)
    {
        $this->authorize('update', $bankrollTransaction);

        $bankrollTransaction->update([
            'amount' => $request->amount
        ]);

        return [
            'success' => true
        ];
    }

    /**
    * DELETE method to delete a Bankroll Transaction
    * 
    * @param Bankroll $bankrollTransaction
    * @return json
    */
    public function delete(Bankroll $bankrollTransaction)
    {
        $this->authorize('update', $bankrollTransaction);
        
        $bankrollTransaction->delete();

        return [
            'success' => true
        ];
    }
}
