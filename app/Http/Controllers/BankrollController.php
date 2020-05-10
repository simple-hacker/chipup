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
    * GET method to view a specific bankroll transaction.
    * 
    * @param Bankroll $bankrollTransaction
    * @return json
    */
    public function view(Bankroll $bankrollTransaction)
    {
        $this->authorize('manage', $bankrollTransaction);

        return response()->json([
            'success' => true,
            'bankrollTransaction' => $bankrollTransaction
        ]);
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
        $this->authorize('manage', $bankrollTransaction);

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
        $this->authorize('manage', $bankrollTransaction);      

        return [
            'success' => $bankrollTransaction->delete()
        ];
    }
}
