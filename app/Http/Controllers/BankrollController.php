<?php

namespace App\Http\Controllers;

use App\Transactions\Bankroll;
use App\Http\Requests\BankrollTransactionRequest;

class BankrollController extends Controller
{
    /**
    * GET method to retrieve user's bankroll transactions
    * 
    * @param BankrollTransactionRequest $request
    * @return json
    */
    public function index()
    {
        return ['bankrollTransactions' => auth()->user()->bankrollTransactions];
    }

    /**
    * POST method to add to the user's bankroll.
    * 
    * @param BankrollTransactionRequest $request
    * @return json
    */
    public function create(BankrollTransactionRequest $request)
    {
        $bankrollTransaction = auth()->user()->createBankrollTransaction($request->validated());

        return [
            'success' => true,
            'bankrollTransaction' => $bankrollTransaction->unsetRelation('user')
            // The user relation is added on because we modify user in BankrollTransactionObserver
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

        $bankrollTransaction->update($request->validated());

        return [
            'success' => true,
            'bankrollTransaction' => $bankrollTransaction->unsetRelation('user')
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

        return [
            'success' => $bankrollTransaction->delete()
        ];
    }
}