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

class CashGameTest extends TestCase
{
    use RefreshDatabase;

    public function testUserMustBeLoggedInToStartCashGame()
    {
        factory('App\User')->create();

        $this->postJson(route('cash.start'))
                ->assertUnauthorized();

        $this->getJson(route('cash.current'))
                ->assertUnauthorized();
    }

    public function testUserCanStartALiveCashGame()
    {     
        $user = $this->signIn();

        $this->postJson(route('cash.start'), $this->getCashGameAttributes())
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

        $this->postJson(route('cash.start'), $this->getCashGameAttributes(1000, $start_time));
        $cash_game = CashGame::first();

        $this->assertEquals($start_time, $cash_game->start_time);
    }

    public function testUserCannotStartACashGameInTheFuture()
    {
        $user = $this->signIn();

        // We'll be passing a Y-m-d H:i:s string from the front end.
        $start_time = Carbon::create('+1 hours')->toDateTimeString();

        $this->postJson(route('cash.start'), $this->getCashGameAttributes(1000, $start_time))
                ->assertStatus(422);
    }

    public function testUserCanGetTheirCurrentLiveCashGame()
    {
        $user = $this->signIn();
        // Start CashGame
        $this->post(route('cash.start'), $this->getCashGameAttributes());

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

    public function testGettingUsersLiveCashGameWhenNotStartedResultsInNull()
    {
        $user = $this->signIn();
        // Don't start CashGame

        $this->getJson(route('cash.current'))
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
        $this->postJson(route('cash.start'), $this->getCashGameAttributes());
        
        // Start the second cash game without finishing the other one
        $this->postJson(route('cash.start'), $this->getCashGameAttributes())
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
        $this->postJson(route('cash.start'), $this->getCashGameAttributes());

        //  End the cash game
        $this->postJson(route('cash.end'), ['amount' => 1000])
                ->assertOk();

        $cash_game = $user->cashGames()->first();

        $this->assertEquals($cash_game->end_time, Carbon::now()->toDateTimeString());      
    }

    public function testUserCanEndACashGameAtASuppliedTime()
    {
        $this->withoutExceptionHandling();

        $user = $this->signIn();

        // Start one cash game
        $this->postJson(route('cash.start'), $this->getCashGameAttributes())
                ->assertOk();

        $end_time = Carbon::create('+30 mins')->toDateTimeString();

        // End the cash game
        $this->postJson(route('cash.end'), [
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
        $this->postJson(route('cash.start'), $this->getCashGameAttributes())
                ->assertOk();

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

    public function testUserCannotEndACashGameWhichDoesntExist()
    {
        $user = $this->signIn();

        // Don't Start Cash Game

        // End the cash game
        $this->postJson(route('cash.end'), ['amount' => 1000])
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
        $this->postJson(route('cash.start'), $this->getCashGameAttributes(1000, $start_time))
                ->assertOk();

        // End the cash game at $end_time which is before $start_time
        $this->postJson(route('cash.end'), ['end_time' => $end_time, 'amount' => 1000])
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
        $this->postJson(route('cash.start'), $this->getCashGameAttributes(5000))
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
        $this->postJson(route('cash.start'), $this->getCashGameAttributes(-1000))
            ->assertStatus(422);

        // Should fail when starting with a float number
        $this->postJson(route('cash.start'), $this->getCashGameAttributes(5.2))
            ->assertStatus(422);

        // Starting with 0 is ok
        $this->postJson(route('cash.start'), $this->getCashGameAttributes(0))
            ->assertOk();
    }

    public function testUserCanSupplyACashOutWhenEndingTheirSession()
    {
        $user = $this->signIn();

        // Start one cash game
        $this->postJson(route('cash.start'), $this->getCashGameAttributes(1000));

        // End the cash game with a CashOut amount
        $this->postJson(route('cash.end'), [
                'amount' => 5000
            ])
            ->assertOk();

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
        $this->postJson(route('cash.start'), $this->getCashGameAttributes());

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

    public function testCashGameAttributesMustBeValidWhenAdding()
    {
        $user = $this->signIn();

        // Stake must be present
        $this->postJson(route('cash.start'), [
                    'amount' => 1000,
                    'variant_id' => Variant::inRandomOrder()->first()->id,
                    'limit_id' => Limit::inRandomOrder()->first()->id,
                    'table_size_id' => TableSize::inRandomOrder()->first()->id,
                    'location' => 'Casino MK',
                ])
                ->assertStatus(422);
        
        // Stake must exist in database
        $this->postJson(route('cash.start'), [
                    'amount' => 1000,
                    'stake_id' => 999,
                    'variant_id' => Variant::inRandomOrder()->first()->id,
                    'limit_id' => Limit::inRandomOrder()->first()->id,
                    'table_size_id' => TableSize::inRandomOrder()->first()->id,
                    'location' => 'Casino MK',
                ])
                ->assertStatus(422);

        // Variant must exist in database
        $this->postJson(route('cash.start'), [
                    'amount' => 1000,
                    'stake_id' => Stake::inRandomOrder()->first()->id,
                    'limit_id' => Limit::inRandomOrder()->first()->id,
                    'table_size_id' => TableSize::inRandomOrder()->first()->id,
                    'location' => 'Casino MK',
                ])
                ->assertStatus(422);

        // Variant must exist in database
        $this->postJson(route('cash.start'), [
                    'amount' => 1000,
                    'stake_id' => Stake::inRandomOrder()->first()->id,
                    'variant_id' => 999,
                    'limit_id' => Limit::inRandomOrder()->first()->id,
                    'table_size_id' => TableSize::inRandomOrder()->first()->id,
                    'location' => 'Casino MK',
                ])
                ->assertStatus(422);

        // Limit must exist in database
        $this->postJson(route('cash.start'), [
                    'amount' => 1000,
                    'stake_id' => Stake::inRandomOrder()->first()->id,
                    'variant_id' => Variant::inRandomOrder()->first()->id,
                    'table_size_id' => TableSize::inRandomOrder()->first()->id,
                    'location' => 'Casino MK',
                ])
                ->assertStatus(422);

        // Limit must exist in database
        $this->postJson(route('cash.start'), [
                    'amount' => 1000,
                    'stake_id' => Stake::inRandomOrder()->first()->id,
                    'limit_id' => 999,
                    'variant_id' => Variant::inRandomOrder()->first()->id,
                    'table_size_id' => TableSize::inRandomOrder()->first()->id,
                    'location' => 'Casino MK',
                ])
                ->assertStatus(422);

        // Table Size must exist in database
        $this->postJson(route('cash.start'), [
                    'amount' => 1000,
                    'stake_id' => Stake::inRandomOrder()->first()->id,
                    'variant_id' => Variant::inRandomOrder()->first()->id,
                    'limit_id' => Limit::inRandomOrder()->first()->id,
                    'location' => 'Casino MK',
                ])
                ->assertStatus(422);

        // Table Size must exist in database
        $this->postJson(route('cash.start'), [
                    'amount' => 1000,
                    'stake_id' => Stake::inRandomOrder()->first()->id,
                    'variant_id' => Variant::inRandomOrder()->first()->id,
                    'limit_id' => Limit::inRandomOrder()->first()->id,
                    'table_size_id' => 999,
                    'location' => 'Casino MK',
                ])
                ->assertStatus(422);

        // Location must be supplied
        $this->postJson(route('cash.start'), [
                    'amount' => 1000,
                    'stake_id' => Stake::inRandomOrder()->first()->id,
                    'variant_id' => Variant::inRandomOrder()->first()->id,
                    'limit_id' => Limit::inRandomOrder()->first()->id,
                    'table_size_id' => TableSize::inRandomOrder()->first()->id,
                ])
                ->assertStatus(422);

        // Location must be a string
        $this->postJson(route('cash.start'), [
                    'amount' => 1000,
                    'stake_id' => Stake::inRandomOrder()->first()->id,
                    'variant_id' => Variant::inRandomOrder()->first()->id,
                    'limit_id' => Limit::inRandomOrder()->first()->id,
                    'table_size_id' => TableSize::inRandomOrder()->first()->id,
                    'location' => 328
                ])
                ->assertStatus(422);
    }

    public function testUserCanGetAllTheirCashGamesAsJson()
    {   
        $user = $this->signIn();

        // Assert response cash games is empty if no cash games exist
        $response = $this->getJson(route('cash.index'))
                ->assertOk()
                ->assertJsonStructure(['success', 'cash_games']);

        $this->assertEmpty($response['cash_games']);

        // Create two cash games
        $this->postJson(route('cash.start'), $this->getCashGameAttributes());
        $user->cashGames()->first()->end();
        $this->postJson(route('cash.start'), $this->getCashGameAttributes());
        $user->cashGames()->get()->last()->end(); 

        // Assert response cash_games returns two cash games
        $response = $this->getJson(route('cash.index'))
                            ->assertOk()
                            ->assertJsonStructure(['success', 'cash_games']);

        $this->assertCount(2, $response['cash_games']);
    }

    public function testAssertNotFoundIfViewingInvalidCashGame()
    {
        $this->signIn();

        // Assert Not Found if supply incorrect cash_game id
        $this->getJson(route('cash.view', ['cash_game' => 99]))
                ->assertNotFound();
    }

    public function testUserCanViewAValidCashGame()
    {
        $cash_game = $this->createCashGame();

        // Assert Not Found if supply incorrect cash_game id
        $this->getJson(route('cash.view', ['cash_game' => $cash_game->id]))
                ->assertOk()
                ->assertJsonStructure(['success', 'cash_game']);
    }

    public function testUserCannotViewAnotherUsersCashGame()
    {
        // Sign in User and create cash game
        $cash_game = $this->createCashGame();

        //Sign in another user
        $this->signIn();

        // Assert Forbidden if cash game does not belong to user
        $this->getJson(route('cash.view', ['cash_game' => $cash_game->id]))
                ->assertForbidden();
    }

    public function testUserCanUpdateTheCashGameDetails()
    {
        $cash_game = $this->createCashGame();

        $start_time = Carbon::create('-1 hour')->toDateTimeString();

        $attributes = [
            'start_time' => $start_time,
            'stake_id' => 2,
            'limit_id' => 2,
            'variant_id' => 2,
            'table_size_id' => 2,
            'comments' => 'New comments',
            'location' => 'Las Vegas',
        ];

        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), $attributes)
                ->assertOk()
                ->assertJsonStructure(['success', 'cash_game'])
                ->assertJson([
                    'success' => true
                ]);

        $this->assertDatabaseHas('cash_games', $attributes);
    }

    public function testUserCannnotUpdateAnotherUsersCashGame()
    {
        $cash_game = $this->createCashGame();
        
        // Sign in as another user
        $this->signIn();

        $attributes = [
            'start_time' => '2020-02-02 18:00:00',
            'stake_id' => 2,
            'limit_id' => 2,
            'variant_id' => 2,
            'table_size_id' => 2,
            'location' => 'Las Vegas',
        ];

        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), $attributes)
                ->assertForbidden();

        $this->assertDatabaseMissing('cash_games', $attributes);
    }

    public function testDataMustBeValidWhenUpdatingACashGame()
    {     
        $cash_game = $this->signIn()->startCashGame($this->getCashGameAttributes());
        $cash_game->end();

        // Empty data is valid
        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), [])->assertOk();

        // Stake Id must exist in stakes table
        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), [
            'stake_id' => 999
        ])->assertStatus(422);

        // Limit Id must exist in limits table
        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), [
            'limit_id' => 999
        ])->assertStatus(422);

        // Variant Id must exist in variants table
        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), [
            'variant_id' => 999
        ])->assertStatus(422);

        // Table_size Id must exist in table_sizes table
        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), [
            'table_size_id' => 999
        ])->assertStatus(422);

        // Start_time cannot be in the future
        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), [
            'start_time' => Carbon::create('+1 second')->toDateTimeString()
        ])->assertStatus(422);

        // end_time cannot be in the future
        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), [
            'end_time' => Carbon::create('+1 second')->toDateTimeString()
        ])->assertStatus(422);

        // Start_time cannot be after end_time
        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), [
            'start_time' => Carbon::create('-10 minutes')->toDateTimeString(),
            'end_time' => Carbon::create('-20 minutes')->toDateTimeString(),
        ])->assertStatus(422);

        // Start_time and end_time cannot be the same
        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), [
            'start_time' => Carbon::create('-10 minutes')->toDateTimeString(),
            'end_time' => Carbon::create('-10 minutes')->toDateTimeString(),
        ])->assertStatus(422);
    }

    public function testUserCanDeleteTheirCashGame()
    {
        $user = $this->signIn();
        $cash_game = $user->startCashGame();

        $this->deleteJson(route('cash.delete', ['cash_game' => $cash_game->id]))
                ->assertOk();

        $this->assertEmpty($user->cashGames);
    }

    public function testUserCannotDeleteAnotherUsersCashGame()
    {
        // Sign in new user and create cash game
        $cash_game = $this->createCashGame();

        //Sign in as another new user
        $this->signIn();

        // User 2 is Forbidden to delete user 1s cash game.
        $this->deleteJson(route('cash.delete', ['cash_game' => $cash_game->id]))
                ->assertForbidden();
    }

    public function testUserCanAddACompletedCashGame()
    {
        $this->withoutExceptionHandling();

        $user = $this->signIn();

        $start_time = Carbon::create('-4 hour')->toDateTimeString();
        $end_time = Carbon::create('-1 hour')->toDateTimeString();

        $attributes = [
            'cash_game' => [
                'start_time' => $start_time,
                'stake_id' => 2,
                'limit_id' => 2,
                'variant_id' => 2,
                'table_size_id' => 2,
                'location' => 'CasinoMK',
            ],
            'buy_ins' => [
                ['amount' => 1000],
                ['amount' => 4000],
                ['amount' => 2500],
            ],
            'expenses' => [
                ['amount' => 500, 'comments' => 'Drinks'],
                ['amount' => 400],
                ['amount' => 750, 'comments' => 'Tips'],
            ],
            'cash_out' => [
                'end_time' => $end_time,
                'amount' => 500,
            ],
        ];

        $this->postJson(route('cash.create'), $attributes)->assertOk();

        $cash_game = $user->cashGames()->first();

        $this->assertCount(1, $user->cashGames);
        $this->assertCount(3, $cash_game->buyIns);
        $this->assertCount(3, $cash_game->expenses);
        $this->assertInstanceOf(CashOut::class, $cash_game->cashOutModel);
    }

    // public function testDataMustBeValidWhenAddingCompletedCashGame()
    // {
        
    // }

    // public function testCannotCreateCashGameIfOneExistsBetweenStartAndEndTimes()
    // {
        
    // }
}
