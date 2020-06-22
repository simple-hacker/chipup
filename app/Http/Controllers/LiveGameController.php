<?php

namespace App\Http\Controllers;

use App\Abstracts\Game;
use Illuminate\Http\Request;
use App\Http\Controllers\GameController;
use App\Http\Requests\EndSessionRequest;
use App\Exceptions\InvalidSessionException;

class LiveGameController extends GameController
{
    protected $game_type;

    /**
    * Returns the User's current Session or null
    * 
    * @return json
    */
    public function current()
    {
        try {
            $game = $this->getLiveSession();

            return response()->json([
                'success' => true,
                'status' => 'live',
                'game' => $game
            ]);

        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    /**
    * POST method to end the current live Session
    * 
    * @param EndSessionRequest $request
    * @return json
    */
    public function end(EndSessionRequest $request)
    {
        try {
            $game = $this->getLiveSession();
            
            if (!$game) {
                $this->throwLiveSessionNotStartedException();
            }

            $game->endAndCashOut($request->end_time, $request->amount ?? 0, $request->currency ?? auth()->user()->id);

            if ($game->game_type === 'tournament') {
                $game->update([
                    'position' => $request->position ?? 0,
                    'entries' => $request->entries ?? 0
                ]);
            }

            return response()->json([
                'success' => true,
                'status' => 'live',
                'game' => $game->fresh()
            ]);
            
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    /**
    * Get the User's current session or return empty array.
    * If the liveSession does not match the game_type of the Controller the api requested
    * throw an InvalidSessionException
    * 
    * @return Game|null
    */
    public function getLiveSession()
    {
        $game = auth()->user()->liveSession() ?? [];

        // // If the retrieved game type is not the game type from which the Controller was fired from
        // // then throw an InvalidSessionException
        // if ($game && $game->game_type !== $this->game_type) {
        //     throw new InvalidSessionException();
        // }

        return $game;
    }
}
