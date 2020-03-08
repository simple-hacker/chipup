<?php

namespace App\Http\Controllers;

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
        return view('setup');
    }

    /**
    * POST method for completing setup
    * 
    * @param SetupRequest $request
    * @return Redirect
    */
    public function complete(SetupRequest $request)
    {
        auth()->user()->update($request->validated());

        auth()->user()->completeSetup();

        return redirect('/dashboard');
    }
}
