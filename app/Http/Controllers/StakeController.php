<?php

namespace App\Http\Controllers;

use App\Attributes\Stake;
use App\Http\Requests\StakeRequest;

class StakeController extends Controller
{
    /**
    * Create a Custom Stake
    * 
    * @param StakeRequest $request
    * @return json
    */
    public function create(StakeRequest $request)
    {
        $attributes = array_merge(['user_id' => auth()->user()->id], $request->validated());

        Stake::create($attributes);

        return response()->json([
            'success' => true,
            'stakes' => auth()->user()->stakes,
        ]);
    }

    /**
    * Retrieve the custom stake
    * 
    * @param Stake $stake
    * @return json
    */
    public function view(Stake $stake)
    {
        // $this->authorize('manage', $stake);

        // return response()->json([
        //     'success' => true,
        //     'transaction' => $stake
        // ]);
    }

    /**
    * Update the custom stake
    * 
    * @param Stake $stake
    * @param StakeRequest $request
    * @return json
    */
    public function update(Stake $stake, StakeRequest $request)
    {
        $this->authorize('manage', $stake);
        
        $stake->update($request->validated());

        return response()->json([
            'success' => true,
            'stakes' => auth()->user()->stakes,
        ]);
    }

    /**
    * Destroy the custom stake
    * 
    * @param Stake $stake
    * @return json
    */
    public function destroy(Stake $stake)
    {
        $this->authorize('manage', $stake);

        return response()->json([
            'success' => $stake->delete(),
        ]);
    }
}
