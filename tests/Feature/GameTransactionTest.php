<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GameTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function testGameTypeAndIdAreRequiredWhenAddingGameTransaction()
    {
        // Just going to test BuyIn on CashGame for the AddGameTransactionRequest required fields

        $cashGame = $this->signIn()->startCashGame();

        // Id is required
        $this->postJson(route('buyin.create'), [
                    'game_type' => $cashGame->game_type,
                    'amount' => 500
                ])
                ->assertStatus(422);

        // Id must be an integer
        $this->postJson(route('buyin.create'), [
                    'id' => 'not an integer',
                    'game_type' => $cashGame->game_type,
                    'amount' => 500
                ])
                ->assertStatus(422);

        // GameType is required
        $this->postJson(route('buyin.create'), [
                    'id' => $cashGame->id,
                    'amount' => 500
                ])
                ->assertStatus(422);

        // GameType must be a string
        $this->postJson(route('buyin.create'), [
                    'id' => $cashGame->id,
                    'game_type' => 111,
                    'amount' => 500
                ])
                ->assertStatus(422);

        // GameType must be the GameType's game_type attribute
        // i.e. cashgame or tournament
        // This is validated in the switch statement in the add method of the GameTransactionController
        // Because we need to load the correct model depending on the string
        $this->postJson(route('buyin.create'), [
                    'id' => $cashGame->id,
                    'game_type' => 'invalid_game_type',
                    'amount' => 500
                ])
                ->assertStatus(422);
    }
}
