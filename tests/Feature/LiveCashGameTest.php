<?php

namespace Tests\Feature;

use App\CashGame;
use Tests\TestCase;
use App\Attributes\Limit;
use App\Attributes\Stake;
use App\Attributes\Variant;
use App\Attributes\TableSize;
use App\Transactions\CashOut;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LiveCashGameTest extends TestCase
{
    use RefreshDatabase;

    public function testUserMustBeLoggedInToStartCashGame()
    {
        factory('App\User')->create();

        $this->postJson(route('cash.live.start'))
                ->assertUnauthorized();

        $this->getJson(route('cash.live.current'))
                ->assertUnauthorized();
    }

    public function testUserCanStartALiveCashGame()
    {     
        $user = $this->signIn();

        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes())
                ->assertOk()
                ->assertJsonStructure(['success', 'cash_game'])
                ->assertJson([
                    'success' => true
                ]);

        $this->assertCount(1, CashGame::all());
        $cash_game = CashGame::first();
        $this->assertEquals($user->id, $cash_game->user_id);
    }

    public function testUserCanStartACashGameAtASpecifiedTime()
    {
        $user = $this->signIn();

        // We'll be passing a Y-m-d H:i:s string from the front end.
        $start_time = Carbon::create('-2 hours')->toDateTimeString();

        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes(1000, $start_time));
        $cash_game = CashGame::first();

        $this->assertEquals($start_time, $cash_game->start_time);
    }

    public function testUserCannotStartACashGameInTheFuture()
    {
        $user = $this->signIn();

        // We'll be passing a Y-m-d H:i:s string from the front end.
        $start_time = Carbon::create('+1 hours')->toDateTimeString();

        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes(1000, $start_time))
                ->assertStatus(422);
    }

    public function testUserCanGetTheirCurrentLiveCashGame()
    {
        $user = $this->signIn();
        // Start CashGame
        $this->post(route('cash.live.start'), $this->getLiveCashGameAttributes());

        $response = $this->getJson(route('cash.live.current'))
                            ->assertJsonStructure(['success', 'status', 'cash_game'])
                            ->assertJson([
                                'success' => true,
                                'status' => 'live'
                            ])
                            ->assertOk();

        $this->assertEquals($response->json()['status'], 'live');
        $this->assertEquals($response->json()['cash_game'], $user->liveCashGame()->toArray());
    }

    public function testGettingUsersLiveCashGameWhenNotStartedResultsInNull()
    {
        $user = $this->signIn();
        // Don't start CashGame

        $this->getJson(route('cash.live.current'))
                ->assertJsonStructure(['success', 'message'])
                ->assertJson([
                    'success' => false
                ]);
                            
        $this->assertEmpty($user->liveCashGame());
    }

    public function testUserCannotStartACashGameWhenThereIsOneInProgress()
    {
        $this->signIn();

        // Start one cash game
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes());
        
        // Start the second cash game without finishing the other one
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes())
                ->assertStatus(422)
                ->assertJsonStructure(['success', 'message'])
                ->assertJson([
                    'success' => false,
                ]);
    }

    public function testUserCanEndACashGame()
    {
        $user = $this->signIn();

        // Start one cash game
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes());

        //  End the cash game
        $this->postJson(route('cash.live.end'), ['amount' => 1000])
                ->assertOk();

        $cash_game = $user->cashGames()->first();

        $this->assertEquals($cash_game->end_time, Carbon::now()->toDateTimeString());      
    }

    public function testUserCanEndACashGameAtASuppliedTime()
    {
        $this->withoutExceptionHandling();

        $user = $this->signIn();

        // Start one cash game
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes())
                ->assertOk();

        $end_time = Carbon::create('+30 mins')->toDateTimeString();

        // End the cash game
        $this->postJson(route('cash.live.end'), [
                    'end_time' => $end_time,
                    'amount' => 1000,
                ])
                ->assertOk();

        $cash_game = $user->cashGames()->first();
        $this->assertEquals($cash_game->end_time, $end_time);
    }

    public function testUserCannotEndACashGameAtAInvalidTime()
    {
        $this->signIn();

        // Start one cash game
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes())
                ->assertOk();

        // End the cash game trying a string and a number
        $this->postJson(route('cash.live.end'), [
                    'end_time' => 'this is not a date'
                ])
                ->assertStatus(422);

        $this->postJson(route('cash.live.end'), [
                    'end_time' => 34732
                ])
                ->assertStatus(422);
    }

    public function testUserCannotEndACashGameWhichDoesntExist()
    {
        $user = $this->signIn();

        // Don't Start Cash Game

        // End the cash game
        $this->postJson(route('cash.live.end'), ['amount' => 1000])
                ->assertStatus(422)
                ->assertJsonStructure(['success', 'message'])
                ->assertJson([
                    'success' => false,
                ]);
    }

    public function testUserCannotEndACashGameBeforeItsStartTime()
    {
        $start_time = Carbon::create('-1 hour')->toDateTimeString();
        $end_time = Carbon::create('-2 hour')->toDateTimeString();

        $this->signIn();

        // Start one cash game at $start_time
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes(1000, $start_time))
                ->assertOk();

        // End the cash game at $end_time which is before $start_time
        $this->postJson(route('cash.live.end'), ['end_time' => $end_time, 'amount' => 1000])
                ->assertStatus(422)
                ->assertJsonStructure(['success', 'message'])
                ->assertJson([
                    'success' => false,
                ]);
    }

    public function testABuyInCanBeSuppliedWhenStartingACashGame()
    {
        $user = $this->signIn();

        // Start one cash game
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes(5000))
            ->assertOk();

        // CashGame profit should be -5000 as it's a BuyIn
        $cash_game = $user->cashGames()->first();
        $this->assertEquals(-5000, $cash_game->profit);
        $this->assertCount(1, $cash_game->buyIns);

        $buy_in = $cash_game->buyIns()->first();
        $this->assertEquals(5000, $buy_in->amount);

        //Check user's bankroll.  It should be 5000 as orignal bankroll is 10000, but we subtract 5000 as it's a buyIn (negative transaction)
        $this->assertEquals(5000, $user->fresh()->bankroll);
    }

    public function testTheBuyInAmountMustBeValidWhenStartingACashGame()
    {
        $this->signIn();

        // Should fail when starting with a negative number
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes(-1000))->assertStatus(422);

        // NOTE: 2020-05-01 Float numbers are valid
        // Should fail when starting with a float number
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes(5.2))->assertOk();

        // Delete the cash game for the test as it was created in assertion above and can only have one running at a time.
        CashGame::first()->delete();
        // Starting with 0 is ok
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes(0))->assertOk();
    }

    public function testUserCanSupplyACashOutWhenEndingTheirSession()
    {
        $user = $this->signIn();

        // Start one cash game
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes(1000));

        // End the cash game with a CashOut amount
        $this->postJson(route('cash.live.end'), ['amount' => 5000])->assertOk();

        // CashGame profit should be 4000. (-1000 buyIn + 5000 cashOut)
        $cash_game = $user->cashGames()->first();
        $this->assertEquals(4000, $cash_game->profit);

        $cash_out = $cash_game->cashOutModel;
        $this->assertInstanceOf(CashOut::class, $cash_out);
        $this->assertEquals(5000, $cash_out->amount);

        //Check user's bankroll.  It should be 14,000 as factory default is 10,000. (Bankroll + CashGame profit)
        $this->assertEquals(14000, $user->fresh()->bankroll);
    }

    public function testTheCashOutAmountMustBeValidWhenEndingACashGame()
    {
        $user = $this->signIn();

        // Start one cash game
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes());

        // Should fail when ending with a negative number
        $this->postJson(route('cash.live.end'), ['amount' => -1000])->assertStatus(422);

        // NOTE: 2020-05-01 Float numbers are now valid.
        $this->postJson(route('cash.live.end'), ['amount' => 54.2])->assertOk();

        // Start another cash game as the one above was completed in the last assertion
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes());
        // Ending with 0 is ok
        $this->postJson(route('cash.live.end'), ['amount' => 0])->assertOk();
    }

    public function testCashGameAttributesMustBeValidWhenAdding()
    {
        $user = $this->signIn();

        // Stake must be present
        $this->postJson(route('cash.live.start'), [
                    'amount' => 1000,
                    'variant_id' => Variant::inRandomOrder()->first()->id,
                    'limit_id' => Limit::inRandomOrder()->first()->id,
                    'table_size_id' => TableSize::inRandomOrder()->first()->id,
                    'location' => 'Casino MK',
                ])
                ->assertStatus(422);
        
        // Stake must exist in database
        $this->postJson(route('cash.live.start'), [
                    'amount' => 1000,
                    'stake_id' => 999,
                    'variant_id' => Variant::inRandomOrder()->first()->id,
                    'limit_id' => Limit::inRandomOrder()->first()->id,
                    'table_size_id' => TableSize::inRandomOrder()->first()->id,
                    'location' => 'Casino MK',
                ])
                ->assertStatus(422);

        // Variant must exist in database
        $this->postJson(route('cash.live.start'), [
                    'amount' => 1000,
                    'stake_id' => Stake::inRandomOrder()->first()->id,
                    'limit_id' => Limit::inRandomOrder()->first()->id,
                    'table_size_id' => TableSize::inRandomOrder()->first()->id,
                    'location' => 'Casino MK',
                ])
                ->assertStatus(422);

        // Variant must exist in database
        $this->postJson(route('cash.live.start'), [
                    'amount' => 1000,
                    'stake_id' => Stake::inRandomOrder()->first()->id,
                    'variant_id' => 999,
                    'limit_id' => Limit::inRandomOrder()->first()->id,
                    'table_size_id' => TableSize::inRandomOrder()->first()->id,
                    'location' => 'Casino MK',
                ])
                ->assertStatus(422);

        // Limit must exist in database
        $this->postJson(route('cash.live.start'), [
                    'amount' => 1000,
                    'stake_id' => Stake::inRandomOrder()->first()->id,
                    'variant_id' => Variant::inRandomOrder()->first()->id,
                    'table_size_id' => TableSize::inRandomOrder()->first()->id,
                    'location' => 'Casino MK',
                ])
                ->assertStatus(422);

        // Limit must exist in database
        $this->postJson(route('cash.live.start'), [
                    'amount' => 1000,
                    'stake_id' => Stake::inRandomOrder()->first()->id,
                    'limit_id' => 999,
                    'variant_id' => Variant::inRandomOrder()->first()->id,
                    'table_size_id' => TableSize::inRandomOrder()->first()->id,
                    'location' => 'Casino MK',
                ])
                ->assertStatus(422);

        // Table Size must exist in database
        $this->postJson(route('cash.live.start'), [
                    'amount' => 1000,
                    'stake_id' => Stake::inRandomOrder()->first()->id,
                    'variant_id' => Variant::inRandomOrder()->first()->id,
                    'limit_id' => Limit::inRandomOrder()->first()->id,
                    'location' => 'Casino MK',
                ])
                ->assertStatus(422);

        // Table Size must exist in database
        $this->postJson(route('cash.live.start'), [
                    'amount' => 1000,
                    'stake_id' => Stake::inRandomOrder()->first()->id,
                    'variant_id' => Variant::inRandomOrder()->first()->id,
                    'limit_id' => Limit::inRandomOrder()->first()->id,
                    'table_size_id' => 999,
                    'location' => 'Casino MK',
                ])
                ->assertStatus(422);

        // Location must be supplied
        $this->postJson(route('cash.live.start'), [
                    'amount' => 1000,
                    'stake_id' => Stake::inRandomOrder()->first()->id,
                    'variant_id' => Variant::inRandomOrder()->first()->id,
                    'limit_id' => Limit::inRandomOrder()->first()->id,
                    'table_size_id' => TableSize::inRandomOrder()->first()->id,
                ])
                ->assertStatus(422);

        // Location must be a string
        $this->postJson(route('cash.live.start'), [
                    'amount' => 1000,
                    'stake_id' => Stake::inRandomOrder()->first()->id,
                    'variant_id' => Variant::inRandomOrder()->first()->id,
                    'limit_id' => Limit::inRandomOrder()->first()->id,
                    'table_size_id' => TableSize::inRandomOrder()->first()->id,
                    'location' => 328
                ])
                ->assertStatus(422);
    }

    public function testUpdatingANonExistingLiveCashGameResultsInError()
    {
        // Sign in user but don't start cash game.
        $this->signIn();

        $attributes = [
            'start_time' => Carbon::create('-1 hour')->toDateTimeString(),
            'stake_id' => 2,
            'limit_id' => 2,
            'variant_id' => 2,
            'table_size_id' => 2,
            'comments' => 'New comments',
            'location' => 'Las Vegas',
        ];

        // Try to update live cash game which has not been started.
        $this->patchJson(route('cash.live.update'), $attributes)->assertStatus(422);
    }
    

    public function testUserCanUpdateTheLiveCashGameDetails()
    {
        $this->createCashGame();

        $attributes = [
            'start_time' => Carbon::create('-1 hour')->toDateTimeString(),
            'stake_id' => 2,
            'limit_id' => 2,
            'variant_id' => 2,
            'table_size_id' => 2,
            'comments' => 'New comments',
            'location' => 'Las Vegas',
        ];

        $this->patchJson(route('cash.live.update'), $attributes)
                ->assertOk()
                ->assertJsonStructure(['success', 'cash_game'])
                ->assertJson([
                    'success' => true
                ]);

        $this->assertCount(1, CashGame::all());
        $this->assertDatabaseHas('cash_games', $attributes);
    }

    public function testDataMustBeValidWhenUpdatingALiveCashGame()
    {     
        $this->signIn()->startCashGame($this->getLiveCashGameAttributes());

        // Empty data is valid
        $this->patchJson(route('cash.live.update'), [])->assertOk();

        // Stake Id must exist in stakes table
        $this->patchJson(route('cash.live.update'), [
            'stake_id' => 999
        ])->assertStatus(422);

        // Limit Id must exist in limits table
        $this->patchJson(route('cash.live.update'), [
            'limit_id' => 999
        ])->assertStatus(422);

        // Variant Id must exist in variants table
        $this->patchJson(route('cash.live.update'), [
            'variant_id' => 999
        ])->assertStatus(422);

        // Table_size Id must exist in table_sizes table
        $this->patchJson(route('cash.live.update'), [
            'table_size_id' => 999
        ])->assertStatus(422);

        // Start_time cannot be in the future
        $this->patchJson(route('cash.live.update'), [
            'start_time' => Carbon::create('+1 second')->toDateTimeString()
        ])->assertStatus(422);

        // end_time cannot be in the future
        $this->patchJson(route('cash.live.update'), [
            'end_time' => Carbon::create('+1 second')->toDateTimeString()
        ])->assertStatus(422);

        // Start_time cannot be after end_time
        $this->patchJson(route('cash.live.update'), [
            'start_time' => Carbon::create('-10 minutes')->toDateTimeString(),
            'end_time' => Carbon::create('-20 minutes')->toDateTimeString(),
        ])->assertStatus(422);

        // Start_time and end_time cannot be the same
        $this->patchJson(route('cash.live.update'), [
            'start_time' => Carbon::create('-10 minutes')->toDateTimeString(),
            'end_time' => Carbon::create('-10 minutes')->toDateTimeString(),
        ])->assertStatus(422);
    }   
}
