<?php

namespace Tests\Feature;

use App\CashGame;
use Tests\TestCase;
use App\Transactions\CashOut;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CashGameTest extends TestCase
{
    use RefreshDatabase;

    public function testAUserMustBeLoggedInToStartCashGame()
    {
        factory('App\User')->create();

        $this->post(route('cash.start'))
                ->assertRedirect('login');

        $this->get(route('cash.current'))
                ->assertRedirect('login');
    }

    public function testAUserCanStartALiveCashGame()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);

        $this->postJson(route('cash.start'))
                ->assertOk()
                ->assertJson([
                    'success' => true
                ]);

        $this->assertCount(1, CashGame::all());
        $cash_game = CashGame::first();
        $this->assertEquals($user->id, $cash_game->user_id);
    }

    public function testAUserCanStartACashGameAtASpecifiedTime()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // We'll be passing a Y-m-d H:i:s string from the front end.
        $start_time = Carbon::create('-2 hours')->toDateTimeString();

        $this->postJson(route('cash.start'), [
            'start_time' => $start_time
        ]);
        $cash_game = CashGame::first();

        $this->assertEquals($start_time, $cash_game->start_time);
    }

    public function testAUserCannotStartACashGameInTheFuture()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // We'll be passing a Y-m-d H:i:s string from the front end.
        $start_time = Carbon::create('+1 hours')->toDateTimeString();

        $this->postJson(route('cash.start'), [
                    'start_time' => $start_time
                ])
                ->assertStatus(422);
    }

    public function testAUserCanGetTheirCurrentLiveCashGame()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);
        $this->post(route('cash.start'));

        $response = $this->getJson(route('cash.current'))
                            ->assertJsonStructure(['success', 'status', 'cash_game'])
                            ->assertJson([
                                'success' => true,
                                'status' => 'live'
                            ])
                            ->assertOk();

        $this->assertEquals($response->json()['status'], 'live');
        $this->assertEquals($response->json()['cash_game'], $user->liveCashGame()->toArray());
    }

    public function testGettingAUsersLiveCashGameWhenNotStartedResultsInNull()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);
        // Don't start CashGame

        $response = $this->getJson(route('cash.current'))
                            ->assertJsonStructure(['success', 'message'])
                            ->assertJson([
                                'success' => false
                            ]);
                            
        $this->assertEmpty($user->liveCashGame());
    }

    public function testAUserCannotStartACashGameWhenThereIsOneInProgress()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // Start one cash game
        $this->postJson(route('cash.start'));
        
        // Start the second cash game without finishing the other one
        $this->postJson(route('cash.start'))
                ->assertStatus(422)
                ->assertJsonStructure(['success', 'message'])
                ->assertJson([
                    'success' => false,
                ]);
    }

    public function testAUserCanEndACashGame()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // Start one cash game
        $this->postJson(route('cash.start'));

        //  End the cash game
        $response = $this->postJson(route('cash.end'))
                ->assertOk();

        $cash_game = $user->cashGames()->first();

        $this->assertEquals($cash_game->end_time, Carbon::now()->toDateTimeString());      
    }

    public function testAUserCanEndACashGameAtASuppliedTime()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // Start one cash game
        $this->postJson(route('cash.start'));

        $end_time = Carbon::create('+1 hour')->toDateTimeString();

        // End the cash game
        $this->postJson(route('cash.end'), [
                    'end_time' => $end_time
                ])
                ->assertOk();

        $cash_game = $user->cashGames()->first();
        $this->assertEquals($cash_game->end_time, $end_time);
    }

    public function testAUserCannotEndACashGameAtAInvalidTime()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // Start one cash game
        $this->postJson(route('cash.start'));

        // End the cash game trying a string and a number
        $this->postJson(route('cash.end'), [
                    'end_time' => 'this is not a date'
                ])
                ->assertStatus(422);

        $this->postJson(route('cash.end'), [
                    'end_time' => 34732
                ])
                ->assertStatus(422);
    }

    public function testAUserCannotEndACashGameWhichDoesntExist()
    {
        $this->withoutExceptionHandling();

        $user = factory('App\User')->create();
        $this->actingAs($user);

        // Don't Start Cash Game

        // End the cash game
        $this->postJson(route('cash.end'))
                ->assertStatus(422)
                ->assertJsonStructure(['success', 'message'])
                ->assertJson([
                    'success' => false,
                ]);
    }

    public function testAUserCannotEndACashGameBeforeItsStartTime()
    {
        $start_time = Carbon::create('-1 hour')->toDateTimeString();
        $end_time = Carbon::create('-2 hour')->toDateTimeString();

        $user = factory('App\User')->create();
        $this->actingAs($user);

        // Start one cash game at $start_time
        $this->postJson(route('cash.start'), ['start_time' => $start_time]);

        // End the cash game at $end_time which is before $start_time
        $this->postJson(route('cash.end'), ['end_time' => $end_time])
                ->assertStatus(422)
                ->assertJsonStructure(['success', 'message'])
                ->assertJson([
                    'success' => false,
                ]);
    }

    public function testABuyInCanBeSuppliedWhenStartingACashGame()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // Start one cash game
        $this->postJson(route('cash.start'), [
                'amount' => 5000
            ])
            ->assertOk();

        // CashGame profit should be -5000 as it's a BuyIn
        $cash_game = $user->cashGames()->first();
        $this->assertEquals(-5000, $cash_game->profit);
        $this->assertCount(1, $cash_game->buyIns);

        $buy_in = $cash_game->buyIns()->first();
        $this->assertEquals(5000, $buy_in->amount);

        // TODO::  CHECK IF BANKROLL IS UPDATED TOO
        //Check user's bankroll.  It should be 15,000 as factory default is 10,000
        // $this->assertEquals(15000, $user->fresh()->bankroll);
    }

    public function testTheBuyInAmountMustBeValidWhenStartingACashGame()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // Should fail when starting with a negative number
        $this->postJson(route('cash.start'), [
                'amount' => -1000
            ])
            ->assertStatus(422);

        // Should fail when starting with a float number
        $this->postJson(route('cash.start'), [
                'amount' => 54.2
            ])
            ->assertStatus(422);

        // Starting with 0 is ok
        $this->postJson(route('cash.start'), [
                'amount' => 0
            ])
            ->assertOk();
    }

    public function testAUserCanSupplyACashOutWhenEndingTheirSession()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // Start one cash game
        $this->postJson(route('cash.start'));

        // End the cash game with a CashOut amount
        $this->postJson(route('cash.end'), [
                'amount' => 5000
            ])
            ->assertOk();

        $cash_game = $user->cashGames()->first();
        $this->assertEquals(5000, $cash_game->profit);

        $cash_out = $cash_game->cashOutModel;
        $this->assertInstanceOf(CashOut::class, $cash_out);
        $this->assertEquals(5000, $cash_out->amount);

        // TODO::  CHECK IF BANKROLL IS UPDATED TOO
        //Check user's bankroll.  It should be 15,000 as factory default is 10,000
        // $this->assertEquals(15000, $user->fresh()->bankroll);
    }

    public function testTheCashOutAmountMustBeValidWhenEndingACashGame()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // Start one cash game
        $this->postJson(route('cash.start'));

        // Should fail when ending with a negative number
        $this->postJson(route('cash.end'), [
                'amount' => -1000
            ])
            ->assertStatus(422);

        // Should fail when ending with a float number
        $this->postJson(route('cash.end'), [
                'amount' => 54.2
            ])
            ->assertStatus(422);

        // Ending with 0 is ok
        $this->postJson(route('cash.end'), [
                'amount' => 0
            ])
            ->assertOk();
    }
}
