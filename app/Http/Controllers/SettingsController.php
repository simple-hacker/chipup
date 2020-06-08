<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UpdateDefaultValuesRequest;

class SettingsController extends Controller
{
    /**
    * POST method to update the authenticated user's email
    * 
    * @param Request $request
    * @return response
    */
    public function updateEmailAddress(Request $request)
    {
        $attributes = $request->validate([
            'email' => ['required', 'email', Rule::unique('users')->ignore(auth()->user())]
        ]);

        return response()->json([
            'success' => auth()->user()->update($attributes),
        ]);
    }

    /**
    * POST method to update the authenticated user's default values
    * 
    * @param UpdateDefaultValuesRequest $request
    * @return response
    */
    public function updateDefaultValues(UpdateDefaultValuesRequest $request)
    {
        return response()->json([
            'success' => auth()->user()->update($request->validated()),
        ]);
    }

    /**
    * POST method to update the authenticated user's password
    * 
    * @param ChangePasswordRequest $request
    * @return response
    */
    public function updatePassword(ChangePasswordRequest $request)
    {
        $hashedNewPassword = Hash::make($request->input('new_password'));

        return response()->json([
            'success' => auth()->user()->update(['password' => $hashedNewPassword]),
        ]);
    }
}
