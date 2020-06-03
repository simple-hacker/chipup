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

    public function testUserCanGetAllTheirCashGamesAsJson()
    {   
        $this->signIn();

        // Assert response cash games is empty if no cash games exist
        $response = $this->getJson(route('cash.index'))
                ->assertOk()
                ->assertJsonStructure(['success', 'cash_games']);

        $this->assertEmpty($response['cash_games']);

        // Create two cash games
        $this->postJson(route('cash.create'), $this->getCashGameAttributes(150, Carbon::create('-3 hour')->toDateTimeString(), Carbon::create('-2 hour')->toDateTimeString()));
        $this->postJson(route('cash.create'), $this->getCashGameAttributes(200, Carbon::create('-6 hour')->toDateTimeString(), Carbon::create('-5 hour')->toDateTimeString()));

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
        $this->getJson(route('cash.view', ['cash_game' => 99]))->assertNotFound();
    }

    public function testUserCanViewAValidCashGame()
    {
        $cash_game = $this->startLiveCashGame();

        // Assert Not Found if supply incorrect cash_game id
        $this->getJson(route('cash.view', ['cash_game' => $cash_game->id]))
                ->assertOk()
                ->assertJsonStructure(['success', 'cash_game']);
    }

    public function testUserCannotViewAnotherUsersCashGame()
    {
        // Sign in User and create cash game
        $cash_game = $this->startLiveCashGame();

        //Sign in another user
        $this->signIn();

        // Assert Forbidden if cash game does not belong to user
        $this->getJson(route('cash.view', ['cash_game' => $cash_game->id]))->assertForbidden();
    }

    public function testUserCanAddACompletedCashGame()
    {
        $user = $this->signIn();

        $attributes = $this->getCashGameAttributes();

        $this->postJson(route('cash.create'), $attributes)->assertOk();

        $cash_game = $user->cashGames()->first();

        $this->assertCount(1, $user->cashGames);
        $this->assertCount(1, $cash_game->buyIns);
        $this->assertCount(2, $cash_game->expenses);
        $this->assertInstanceOf(CashOut::class, $cash_game->cashOutModel);
    }

    public function testBuyInMustBeSuppliedWhenAddingACompletedCashGame()
    {
        $this->signIn();

        $attributes = $this->getCashGameAttributes();
        unset($attributes['buy_ins']);

        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
    }

    public function testIfNoCashOutIsSuppliedThenItDefaultsToZero()
    {
        $user = $this->signIn();

        $attributes = $this->getCashGameAttributes();
        unset($attributes['cash_out_model']);

        $this->postJson(route('cash.create'), $attributes)->assertOk();

        $cash_game = $user->cashGames()->first();

        $this->assertEquals($cash_game->cashOutModel->amount, 0);
    }

    public function testUserCanAddCompletedCashGameBeforeALiveCashGame()
    {
        $this->signIn();

        // Start a Live CashGame 10 minutes ago.
        $live_start_time = Carbon::now()->subMinutes(10)->toDateTimeString();
        $this->post(route('cash.live.start'), $this->getLiveCashGameAttributes(1000, $live_start_time));

        // Create a completed Cash Game with start_time 5 hours ago and end_time 4 hours ago
        $start_time = Carbon::now()->subHours(5)->toDateTimeString();
        $end_time = Carbon::now()->subHours(4)->toDateTimeString();
        $attributes = $this->getCashGameAttributes(100, $start_time, $end_time);

        // Assert Okay because it is not conflicting the live cash game.
        $this->postJson(route('cash.create'), $attributes)->assertOk();
    }

    public function testDataMustBeValidWhenAddingCompletedCashGame()
    {
        // NOTE: Need to delete CashGame after every assertOk as it uses the same start and end times and unable
        // to create a CashGame if it clashes with another one.

        $this->signIn();

        // Valid attributes.  Will change each one before testing.
        $valid_attributes = $this->getCashGameAttributes();

        // Variables need to be valid.  Only testing one variable as rules are the same.
        $attributes = $valid_attributes;
        $attributes['stake_id'] = 99; // 99 does not exist.
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
        unset($attributes['stake_id']); // Do not provide stake_id
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // Location is required.
        $attributes = $valid_attributes;
        unset($attributes['location']);
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // BuyIns must be supplied.
        $attributes = $valid_attributes;

        // BuyIn must be an integer.
        $attributes['buy_ins'][0]['amount'] = 'Not an integer';
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // BuyIns cannot be negative.
        $attributes['buy_ins'][0]['amount'] = -1000;
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
        // BuyIn cannot be zero
        $attributes['buy_ins'][0]['amount'] = 0;
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        //BuyIn must be present
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
        // Amounts cannot be 0.
        $attributes['expenses'][0]['amount'] = 0;
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
        $attributes['cash_out_model']['amount'] = 'Not an integer';
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
        $attributes['cash_out_model']['amount'] = -1000;
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // Float numbers are okay.
        // NOTE: 2020-04-01 Float amounts are valid
        $attributes['cash_out_model']['amount'] = 56.69;
        $this->postJson(route('cash.create'), $attributes)->assertOk();

        // CashOut amounts CAN be zero as you can bust cash game
        CashGame::first()->delete();        
        $attributes['cash_out_model']['amount'] = 0;
        $this->postJson(route('cash.create'), $attributes)->assertOk();
    }

    public function testCanUpdateJustTheBasicDetailsWithoutTransactions()
    {
        $this->withoutExceptionHandling();

        $user = $this->signIn();
        $this->postJson(route('cash.create'), $this->getCashGameAttributes())->assertOk();
        $cash_game = $user->cashGames->first();

        // Send only a new location.
        $attributes = [
            'location' => 'New Location'
        ];

        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), $attributes)->assertOk();
        $this->assertEquals('New Location', $cash_game->fresh()->location);
    }

    public function testStartAndEndTimesMustBeValidWhenAddingACompletedCashGame()
    {
        $this->signIn();

        // Valid attributes.  Will change each one before testing.
        $valid_attributes = $this->getCashGameAttributes();

        //Start time is required
        $attributes = $valid_attributes;
        unset($attributes['start_time']);
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // Start Time cannot be in the future
        $attributes = $valid_attributes;
        $attributes['start_time'] = Carbon::create('+1 seconds')->toDateTimeString();
        unset($attributes['cash_out']);
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // NOTE: End Time is now required.
        $attributes = $valid_attributes;
        unset($attributes['end_time']);
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // End Time cannot be in the future
        $attributes = $valid_attributes;
        $attributes['end_time'] = Carbon::create('+1 seconds')->toDateTimeString();
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // End Time cannot be before Start Time
        $attributes = $valid_attributes;
        $attributes['start_time'] = Carbon::now()->subMinutes(10)->toDateTimeString();
        $attributes['end_time'] = Carbon::now()->subMinutes(30)->toDateTimeString();
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // Start Time and End Time must not be the same
        $attributes = $valid_attributes;
        $attributes['start_time'] = Carbon::now()->subMinutes(10)->toDateTimeString();
        $attributes['end_time'] = Carbon::now()->subMinutes(10)->toDateTimeString();
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
    }

    public function testCannotCreateCashGameIfOneExistsBetweenStartAndEndTimes()
    {
        $this->signIn();

        $start_time = Carbon::create(2020, 04, 27, 18, 0, 0)->toDateTimeString();
        $end_time = Carbon::create(2020, 04, 27, 20, 0, 0)->toDateTimeString();
        $valid_attributes = $this->getCashGameAttributes(100, $start_time, $end_time);

        // Create cash game between 2020-04-27 18:00:00 and 2020-04-27 20:00:00
        $this->postJson(route('cash.create'), $valid_attributes)->assertOk();

        // Creating a cash game at 2020-04-27 19:00:00 will fail.
        $attributes = $valid_attributes;
        $attributes['start_time'] = Carbon::create(2020, 04, 27, 19, 30, 0)->toDateTimeString();
        $attributes['end_time'] = Carbon::create(2020, 04, 27, 21, 30, 00)->toDateTimeString();
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // Creating a cash game with the same start_time (18:00:00) will fail.
        $attributes = $valid_attributes;
        $attributes['start_time'] = Carbon::create(2020, 04, 27, 18, 0, 0)->toDateTimeString();
        $attributes['end_time'] = Carbon::create(2020, 04, 27, 21, 30, 00)->toDateTimeString();
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // Creating a cash game where its start_time at an already exisiting end_time (20:00:00) will fail.
        $attributes = $valid_attributes;
        $attributes['start_time'] = Carbon::create(2020, 04, 27, 20, 00, 00)->toDateTimeString();
        $attributes['end_time'] = Carbon::create(2020, 04, 27, 21, 30, 00)->toDateTimeString();
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
    }

    public function testUserCanDeleteTheirCashGame()
    {
        $user = $this->signIn();
        $cash_game = $user->startCashGame();

        $this->deleteJson(route('cash.delete', ['cash_game' => $cash_game->id]))->assertOk();

        $this->assertEmpty($user->cashGames);
    }

    public function testUserCannotDeleteAnotherUsersCashGame()
    {
        // Sign in new user and create cash game
        $cash_game = $this->startLiveCashGame();

        //Sign in as another new user
        $this->signIn();

        // User 2 is Forbidden to delete user 1s cash game.
        $this->deleteJson(route('cash.delete', ['cash_game' => $cash_game->id]))->assertForbidden();
    }

    public function testCannotAddCompletedCashGameDuringLiveCashGame()
    {
        $this->signIn();

        // Start a Live CashGame 30 minutes ago.
        $live_start_time = Carbon::now()->subMinutes(30)->toDateTimeString();
        $this->post(route('cash.live.start'), $this->getLiveCashGameAttributes(1000, $live_start_time));

        // Create a completed Cash Game with start_time 10 mins ago and end_time 5 mins ago
        // So completed Cash Game is conflicting with the Live Cash Game.
        $attributes = [
            'start_time' => Carbon::now()->subMinutes(10)->toDateTimeString(),
            'stake_id' => 2,
            'limit_id' => 2,
            'variant_id' => 2,
            'table_size_id' => 2,
            'location' => 'CasinoMK',
            'end_time' => Carbon::now()->subMinutes(5)->toDateTimeString(),
        ];
        // Assert 422 because it conflicts
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
    }

    public function testUserCannnotUpdateAnotherUsersCashGame()
    {
        $user = $this->signIn();
        $this->postJson(route('cash.create'), $this->getCashGameAttributes());
        $cash_game = $user->cashGames()->first();

        // Sign in as a different user and try to update cash_game.
        $user2 = $this->signIn();
        $attributes = [
            'location' => 'Las Vegas'
        ];

        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), $attributes)->assertForbidden();
    }

    public function testCashGameCanBeUpdated()
    {
        $user = $this->signIn();
        $this->postJson(route('cash.create'), $this->getCashGameAttributes());
        $cash_game = $user->cashGames()->first();

        $attributes = [
            'stake_id' => 2,
            'limit_id' => 2,
            'variant_id' => 2,
            'table_size_id' => 2,
            'location' => 'New Location',
        ];

        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), $attributes)->assertOk();

        $this->assertDatabaseHas('cash_games', $attributes);
    }
}
