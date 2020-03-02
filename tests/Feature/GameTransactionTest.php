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

        $cash_game = $this->signIn()->startCashGame();

        // Id is required
        $this->postJson(route('buyin.add'), [
                    'game_type' => $cash_game->game_type,
                    'amount' => 500
                ])
                ->assertStatus(422);

        // Id must be an integer
        $this->postJson(route('buyin.add'), [
                    'id' => 'not an integer',
                    'game_type' => $cash_game->game_type,
                    'amount' => 500
                ])
                ->assertStatus(422);

        // GameType is required
        $this->postJson(route('buyin.add'), [
                    'id' => $cash_game->id,
                    'amount' => 500
                ])
                ->assertStatus(422);

        // GameType must be a string
        $this->postJson(route('buyin.add'), [
                    'id' => $cash_game->id,
                    'game_type' => 111,
                    'amount' => 500
                ])
                ->assertStatus(422);

        // GameType must be the GameType's game_type attribute
        // i.e. cashgame or tournament
        // This is validated in the switch statement in the add method of the GameTransactionController
        // Because we need to load the correct model depending on the string
        $this->postJson(route('buyin.add'), [
                    'id' => $cash_game->id,
                    'game_type' => 'invalid_game_type',
                    'amount' => 500
                ])
                ->assertStatus(422);
    }
}
