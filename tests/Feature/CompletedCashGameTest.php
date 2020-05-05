<?php

namespace Tests\Feature;

use App\CashGame;
use Tests\TestCase;
use App\Transactions\CashOut;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompletedCashGameTest extends TestCase
{
    use RefreshDatabase;

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

    public function testUserCanAddACompletedCashGame()
    {
        $user = $this->signIn();

        $attributes = [
            'cash_game' => [
                'start_time' => Carbon::create('-4 hour')->toDateTimeString(),
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
                'end_time' => Carbon::create('-1 hour')->toDateTimeString(),
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

    public function testBuyInMustBeSuppliedWhenAddingACompletedCashGame()
    {
       $this->signIn();

        $attributes = [
            'cash_game' => [
                'start_time' => Carbon::create('-4 hour')->toDateTimeString(),
                'stake_id' => 2,
                'limit_id' => 2,
                'variant_id' => 2,
                'table_size_id' => 2,
                'location' => 'CasinoMK',
            ],
            // No BuyIn provided
            'cash_out' => [
                'end_time' => Carbon::create('-1 hour')->toDateTimeString(),
                'amount' => 500,
            ],
        ];

        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
    }

    public function testIfNoCashOutIsSuppliedThenItDefaultsToNowandZero()
    {
        $user = $this->signIn();

        $start_time = Carbon::create(2020, 04, 28, 18, 30, 0)->toDateTimeString();
        $testNow = Carbon::create(2020, 04, 28, 20, 45, 0);

        Carbon::setTestNow($testNow);

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
                ['amount' => 1000]
            ],
            // No CashOut provided
        ];

        $this->postJson(route('cash.create'), $attributes)->assertOk();

        $cash_game = $user->cashGames()->first();

        $this->assertEquals($cash_game->cashOutModel->amount, 0);
        $this->assertEquals($cash_game->end_time, $testNow->toDateTimeString());
    }

    public function testUserCanAddCompletedCashGameBeforeALiveCashGame()
    {
        $user = $this->signIn();

        // Start a Live CashGame 10 minutes ago.
        $live_start_time = Carbon::now()->subMinutes(10)->toDateTimeString();
        $this->post(route('cash.start'), $this->getCashGameAttributes(1000, $live_start_time));

        // Create a completed Cash Game with start_time 5 hours ago and end_time 4 hours ago
        $attributes = [
            'cash_game' => [
                'start_time' => Carbon::now()->subHours(5)->toDateTimeString(),
                'stake_id' => 2,
                'limit_id' => 2,
                'variant_id' => 2,
                'table_size_id' => 2,
                'location' => 'CasinoMK',
            ],
            'buy_ins' => [
                ['amount' => 2500]
            ],
            'cash_out' => [
                'end_time' => Carbon::now()->subHours(4)->toDateTimeString(),
                'amount' => 1000,
            ]
        ];
        // Assert Okay because it is not conflicting the live cash game.
        $this->postJson(route('cash.create'), $attributes)->assertOk();
    }

    public function testDataMustBeValidWhenAddingCompletedCashGame()
    {
        // NOTE: Need to delete CashGame after every assertOk as it uses the same start and end times and unable
        // to create a CashGame if it clashes with another one.

        $this->signIn();

        // Valid attributes.  Will change each one before testing.
        $valid_attributes = [
            'cash_game' => [
                'start_time' => Carbon::create('-4 hour')->toDateTimeString(),
                'stake_id' => 2,
                'limit_id' => 2,
                'variant_id' => 2,
                'table_size_id' => 2,
                'location' => 'CasinoMK',
            ],
            'buy_ins' => [
                ['amount' => 1000]
            ],
            'expenses' => [
                ['amount' => 400],
                ['amount' => 750, 'comments' => 'Tips'],
            ],
            'cash_out' => [
                'end_time' => Carbon::create('-1 hour')->toDateTimeString(),
                'amount' => 1000,
            ]
        ];

        // Variables need to be valid.  Only testing one variable as rules are the same.
        $attributes = $valid_attributes;
        $attributes['cash_game']['stake_id'] = 99; // 99 does not exist.
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
        unset($attributes['cash_game']['stake_id']); // Do not provide stake_id
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // Location is required.
        $attributes = $valid_attributes;
        unset($attributes['cash_game']['location']);
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // BuyIns must be supplied.
        $attributes = $valid_attributes;
        $attributes['buy_ins'][0]['amount'] = 'Not an integer';
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
        $attributes['buy_ins'][0]['amount'] = -1000;
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
        unset($attributes['buy_ins'][0]);
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
        // NOTE: 2020-04-01 Float amounts are valid
        $attributes['buy_ins'][0]['amount'] = 56.69;
        $this->postJson(route('cash.create'), $attributes)->assertOk();
        CashGame::first()->delete();

        // Expenses amount must be a non negative integer
        $attributes = $valid_attributes;
        $attributes['expenses'][0]['amount'] = 'Not an integer';
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
        $attributes['expenses'][0]['amount'] = -1000;
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
        // If given empty amount then it default to 0.
        unset($attributes['expenses'][0]['amount']);
        $this->postJson(route('cash.create'), $attributes)->assertOk();
        CashGame::first()->delete();
        // If supply comments without amount, then 422.
        $attributes = $valid_attributes;
        unset($attributes['expenses'][1]['amount']);
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
        // NOTE: 2020-04-01 Float amounts are valid
        $attributes = $valid_attributes;
        $attributes['expenses'][0]['amount'] = 56.69;
        $this->postJson(route('cash.create'), $attributes)->assertOk();
        CashGame::first()->delete();
        
        // CashOut amount must be a non negative integer
        $attributes = $valid_attributes;
        $attributes['cash_out']['amount'] = 'Not an integer';
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
        $attributes['cash_out']['amount'] = -1000;
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
        // NOTE: 2020-04-01 Float amounts are valid
        $attributes['cash_out']['amount'] = 56.69;
        $this->postJson(route('cash.create'), $attributes)->assertOk();
    }

    public function testStartAndEndTimesMustBeValidWhenAddingACompletedCashGame()
    {
        $this->signIn();

        // Valid attributes.  Will change each one before testing.
        $valid_attributes = [
            'cash_game' => [
                'start_time' => Carbon::create('-4 hour')->toDateTimeString(),
                'stake_id' => 2,
                'limit_id' => 2,
                'variant_id' => 2,
                'table_size_id' => 2,
                'location' => 'CasinoMK',
            ],
            'buy_ins' => [
                ['amount' => 1000]
            ],
            'cash_out' => [
                'end_time' => Carbon::create('-1 hour')->toDateTimeString(),
                'amount' => 1000,
            ]
        ];

        //Start time is required
        $attributes = $valid_attributes;
        unset($attributes['cash_game']['start_time']);
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // Start Time cannot be in the future
        $attributes = $valid_attributes;
        $attributes['cash_game']['start_time'] = Carbon::create('+1 seconds')->toDateTimeString();
        unset($attributes['cash_out']);
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // End Time is optional as it defaults to Now()
        $attributes = $valid_attributes;
        unset($attributes['cash_out']['end_time']);
        $this->postJson(route('cash.create'), $attributes)->assertOk();

        // End Time cannot be in the future
        $attributes = $valid_attributes;
        $attributes['cash_out']['end_time'] = Carbon::create('+1 seconds')->toDateTimeString();
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // End Time cannot be before Start Time
        $attributes = $valid_attributes;
        $attributes['cash_game']['start_time'] = Carbon::now()->subMinutes(10)->toDateTimeString();
        $attributes['cash_out']['end_time'] = Carbon::now()->subMinutes(30)->toDateTimeString();
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // Start Time and End Time must not be the same
        $attributes = $valid_attributes;
        $attributes['cash_game']['start_time'] = Carbon::now()->subMinutes(10)->toDateTimeString();
        $attributes['cash_out']['end_time'] = Carbon::now()->subMinutes(10)->toDateTimeString();
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
    }

    public function testCannotCreateCashGameIfOneExistsBetweenStartAndEndTimes()
    {
        $user = $this->signIn();

        $valid_attributes = [
            'cash_game' => [
                'start_time' => Carbon::create(2020, 04, 27, 18, 0, 0)->toDateTimeString(),
                'stake_id' => 2,
                'limit_id' => 2,
                'variant_id' => 2,
                'table_size_id' => 2,
                'location' => 'CasinoMK',
            ],
            'buy_ins' => [
                ['amount' => 1000]
            ],
            'cash_out' => [
                'end_time' => Carbon::create(2020, 04, 27, 20, 0, 0)->toDateTimeString(),
                'amount' => 1000,
            ]
        ];
        // Create cash game between 2020-04-27 18:00:00 and 2020-04-27 20:00:00
        $this->postJson(route('cash.create'), $valid_attributes)->assertOk();

        // Creating a cash game at 2020-04-27 19:00:00 will fail.
        $attributes = $valid_attributes;
        $attributes['cash_game']['start_time'] = Carbon::create(2020, 04, 27, 19, 30, 0)->toDateTimeString();
        $attributes['cash_out']['end_time'] = Carbon::create(2020, 04, 27, 21, 30, 00)->toDateTimeString();
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // Creating a cash game with the same start_time (18:00:00) will fail.
        $attributes = $valid_attributes;
        $attributes['cash_game']['start_time'] = Carbon::create(2020, 04, 27, 18, 0, 0)->toDateTimeString();
        $attributes['cash_out']['end_time'] = Carbon::create(2020, 04, 27, 21, 30, 00)->toDateTimeString();
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // Creating a cash game where its start_time at an already exisiting end_time (20:00:00) will fail.
        $attributes = $valid_attributes;
        $attributes['cash_game']['start_time'] = Carbon::create(2020, 04, 27, 20, 00, 00)->toDateTimeString();
        $attributes['cash_out']['end_time'] = Carbon::create(2020, 04, 27, 21, 30, 00)->toDateTimeString();
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
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
}
