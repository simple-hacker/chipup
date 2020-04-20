<?php

namespace App\Http\Controllers;

use App\Attributes\Limit;
use App\Attributes\Stake;
use App\Attributes\Variant;
use App\Attributes\TableSize;
use App\Http\Requests\SetupRequest;

class SetupController extends Controller
{
    /**
    * GET method to view the index page.
    * 
    * @return View
    */
    public function index()
    {
        $stakes = Stake::all();
        $limits = Limit::all();
        $variants = Variant::all();
        $table_sizes = TableSize::all();
        
        return view('setup', compact('stakes', 'limits', 'variants', 'table_sizes'));
    }

    /**
    * POST method for completing setup
    * 
    * @param SetupRequest $request
    * @return Redirect
    */
    public function complete(SetupRequest $request)
    {
        // Remove bankroll from request because we add a BankrollTransaction through User.
        auth()->user()->update($request->except('bankroll'));

        // If bankroll was supplied during setup, create the Bankroll Transaction which will also update
        // the user's bankroll.
        if ($request->bankroll && $request->bankroll > 0) {
            auth()->user()->createBankrollTransaction(['amount' => $request->bankroll]);
        }

        auth()->user()->completeSetup();

        return response()->json([
            'success' => true,
            'redirect' => route('dashboard'),
        ]);
    }
}
