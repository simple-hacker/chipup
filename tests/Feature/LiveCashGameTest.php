<?php

namespace Tests\Feature;

use App\Models\CashGame;
use Tests\TestCase;
use App\Transactions\CashOut;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LiveCashGameTest extends TestCase
{
    use RefreshDatabase;

    /*
    * ==================================
    * INDEX
    * ==================================
    */

    // User must be logged in start/view/update/end live cash game

    // User can start a live cash game
    // User cannot start another cash game if there is one already live
    // Required data must be valid when starting
    // CashGames do not have any non required attributes
    // User can start at a specified time.
    // If no start time is provided then it starts at current time.
    // Start time cannot be in the future
    // Start time must be valid
    // Cannot start a cash game which clashes with another cash game

    // A BuyIn Can be provided when starting a cash game
    // BuyIn CANNOT be zero for CashGames
    // BuyIn amount must be provided when starting a Cash Game

    // User can view their live cash game
    // Trying to view a Live CashGame when one has not been started is invalid

    // User can update live CashGame
    // User cannot update a live cash game that does not exist
    // User cannot update another user's live cash game
    // Data must be valid when updating live cash game
    // Start date cannot be in the future when updating
    // Cannot update live cash game with new times which clashes with another cash game
    // Updating live cash game's times does not clash with itself
    // User can just update comments

    // User can end a live cash.
    // User can end a live cash game at a specified time
    // User cannot end a live cash game that does not exist.
    // User cannot end a live cash game in the future
    // End time must be valid if provided.
    // User cannot end a live cash game before it's start time
    // User can end a live cash game exactly on its start time
    // If no end time is provided then CashOut at current time
    // If no cash out is provided then it defaults to 0
    // Cash out amount must be valid
    // Position and Entries are ignored when ending a cash game

    /*
    * ==================================
    * TESTS
    * ==================================
    */

    // User must be logged in start/view/update/end live cash game
    public function testUserMustBeLoggedInToStartCashGame()
    {
        $this->postJson(route('cash.live.start'))->assertUnauthorized();
        $this->getJson(route('live.current'))->assertUnauthorized();
        $this->getJson(route('cash.live.update'))->assertUnauthorized();
        $this->getJson(route('live.end'))->assertUnauthorized();
    }

    // User can start a live cash game
    public function testUserCanStartALiveCashGame()
    {
        $user = $this->signIn();

        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes())->assertOk();

        $this->assertCount(1, CashGame::all());
        $cashGame = CashGame::first();
        $this->assertTrue($cashGame->user->is($user));
    }

    // User cannot start another cash game if there is one already live
    public function testUserCannotStartLiveCashGameIfOneIsInProgress()
    {
        $this->signIn();

        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes())->assertOk();

        // Starting another cash game will result in 422
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes())->assertStatus(422);
    }

    // Required data must be valid when starting
    public function testCashGameAttributesMustBeValidWhenStarting()
    {
        $this->signIn();

        $validAttributes = $this->getLiveCashGameAttributes();

        // stake must be supplied
        $attributes = $validAttributes;
        unset($attributes['stake_id']);
        $this->postJson(route('cash.live.start'), $attributes)->assertStatus(422);

        // stake must exist in database
        $attributes = $validAttributes;
        $attributes['variant_id'] = 999;
        $this->postJson(route('cash.live.start'), $attributes)->assertStatus(422);

        // variant must be supplied
        $attributes = $validAttributes;
        unset($attributes['variant_id']);
        $this->postJson(route('cash.live.start'), $attributes)->assertStatus(422);

        // variant must exist in database
        $attributes = $validAttributes;
        $attributes['variant_id'] = 999;
        $this->postJson(route('cash.live.start'), $attributes)->assertStatus(422);

        // limit must be supplied
        $attributes = $validAttributes;
        unset($attributes['limit_id']);
        $this->postJson(route('cash.live.start'), $attributes)->assertStatus(422);

        // limit must exist in database
        $attributes = $validAttributes;
        $attributes['limit_id'] = 999;
        $this->postJson(route('cash.live.start'), $attributes)->assertStatus(422);

        // table_size must be supplied
        $attributes = $validAttributes;
        unset($attributes['table_size_id']);
        $this->postJson(route('cash.live.start'), $attributes)->assertStatus(422);

        // table_size must exist in database
        $attributes = $validAttributes;
        $attributes['table_size_id'] = 999;
        $this->postJson(route('cash.live.start'), $attributes)->assertStatus(422);

        // location must be supplied
        $attributes = $validAttributes;
        unset($attributes['location']);
        $this->postJson(route('cash.live.start'), $attributes)->assertStatus(422);

        // location must be a string
        $attributes = $validAttributes;
        $attributes['location'] = 999;
        $this->postJson(route('cash.live.start'), $attributes)->assertStatus(422);
    }

    public function testNonRequiredCashGameAttributesAreOptionalAndValidWhenStarting()
    {
        // CashGames do not have any non required attributes
        $this->assertTrue(true);
    }

    // User can start at a specified time.
    public function testUserCanStartACashGameAtASpecifiedTime()
    {
        $user = $this->signIn();

        // We'll be passing a Y-m-d H:i:s string from the front end.
        $start_time = Carbon::create('-1 second')->toDateTimeString();

        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes(1000, $start_time));

        $this->assertEquals($start_time, $user->cashGames()->first()->start_time);
    }

    // If no start time is provided then it starts at current time.
    public function testIfNoStartTimeProvidedThenStartCashGameAtCurrentTime()
    {
        $user = $this->signIn();

        // getLiveCashGameAttributes without the start_time parameter has start_time already unset
        $attributes = $this->getLiveCashGameAttributes();
        unset($attributes['start_time']);

        $this->postJson(route('cash.live.start'), $attributes);

        // Set microseconds of now to 0 because microseconds are not saved in database
        $this->assertEquals(now()->micro(0), $user->cashGames()->first()->start_time);
    }

    // Start time cannot be in the future
    public function testUserCannotStartACashGameInTheFuture()
    {
        $this->signIn();

        $attributes = $this->getLiveCashGameAttributes();
        $attributes['start_time'] = Carbon::create('+1 seconds')->toDateTimeString();

        $this->postJson(route('cash.live.start'), $attributes)->assertStatus(422);
    }

    // Start time must be valid
    public function testStartTimeMustBeValid()
    {
        $this->signIn();

        $validAttributes = $this->getLiveCashGameAttributes();

        $attributes = $validAttributes;
        $attributes['start_time'] = 'Not a date';
        $this->postJson(route('cash.live.start'), $attributes)->assertStatus(422);

        $attributes = $validAttributes;
        $attributes['start_time'] = 999;
        $this->postJson(route('cash.live.start'), $attributes)->assertStatus(422);

        $attributes = $validAttributes;
        $attributes['start_time'] = true;
        $this->postJson(route('cash.live.start'), $attributes)->assertStatus(422);

        // Null is valid as it will use current date and time.
    }

    // Cannot start a cash game which clashes with another cash game
    public function testStartTimeCannotClashWithAnotherCashGame()
    {
        // NOTE: Clashes can still occur, if you start one a second before another cash game's start time
        // then it will overrun in to the other cash.
        // But then cashGamesAtTime will return more than 1 which is still > 0

        $user = $this->signIn();
        $dateTime = Carbon::create(2020, 05, 01, 12, 0, 0);

        // Add CashGame to database with times 1st May 2020 12:30 - 14:30
        \App\Models\CashGame::factory()->create([
            'user_id' => $user->id,
            'start_time' => $dateTime->toDateTimeString(),
            'end_time' => $dateTime->copy()->addHours(2)->toDateTimeString()
        ]);

        $validAttributes = $this->getLiveCashGameAttributes();

        // Try starting one at 13:00 (30 minutes after other cash game start_time)
        $attributes = $validAttributes;
        $attributes['start_time'] = $dateTime->copy()->addMinutes(30);
        $this->postJson(route('cash.live.start'), $attributes)->assertStatus(422);

        // Try starting on exactly the start_time
        $attributes = $validAttributes;
        $attributes['start_time'] = $dateTime->toDateTimeString();
        $this->postJson(route('cash.live.start'), $attributes)->assertStatus(422);

        // Try starting on exactly the end_time
        $attributes = $validAttributes;
        $attributes['start_time'] = $dateTime->copy()->addHours(2)->toDateTimeString();
        $this->postJson(route('cash.live.start'), $attributes)->assertStatus(422);
    }

    // A BuyIn Can be provided when starting a cash game
    // This has already been tested in testUserCanStartALiveCashGame
    public function testABuyInCanBeSuppliedWhenStartingACashGame()
    {
        $user = $this->signIn();

        // Start a live cash game
        $attributes = $this->getLiveCashGameAttributes();
        $attributes['amount'] = 1000;
        $this->postJson(route('cash.live.start'), $attributes)->assertOk();

        $cashGame = $user->cashGames()->first();
        // Check BuyIn was created
        $this->assertCount(1, $cashGame->buyIns);
        // Check the amount of the BuyIn is 1000.
        $this->assertEquals(1000, $cashGame->buyIns->first()->amount);
    }

    // BuyIn CANNOT be zero for CashGames
    public function testABuyInCannotBeZeroForFreerollCashGames()
    {
        $this->signIn();

        // Start a live cash game
        $attributes = $this->getLiveCashGameAttributes();
        $attributes['amount'] = 0;
        $this->postJson(route('cash.live.start'), $attributes)->assertStatus(422);
    }

    // BuyIn amount must be provided when starting a Cash Game
    public function testBuyInAmountIsRequired()
    {
        $user = $this->signIn();

        // Start a live cash game without providing amount
        $attributes = $this->getLiveCashGameAttributes();
        unset($attributes['amount']);
        $this->postJson(route('cash.live.start'), $attributes)->assertStatus(422);
    }

    // BuyIn must be valid
    public function testTheBuyInAmountMustBeValidWhenStartingACashGame()
    {
        $this->signIn();

        $validAttributes = $this->getLiveCashGameAttributes();

        // Amount must be an integer.
        $attributes = $validAttributes;
        $attributes['amount'] = 'Not an integer';
        $this->postJson(route('cash.live.start'), $attributes)->assertStatus(422);

        // Amount cannot be negative
        $attributes = $validAttributes;
        $attributes['amount'] = -1;
        $this->postJson(route('cash.live.start'), $attributes)->assertStatus(422);

        // Float numbers are valid.
        $attributes = $validAttributes;
        $attributes['amount'] = 10.55;
        $this->postJson(route('cash.live.start'), $attributes)->assertOk();
    }

    // User can view their live cash game
    public function testUserCanViewTheirLiveCashGame()
    {
        $user = $this->signIn();

        $this->post(route('cash.live.start'), $this->getLiveCashGameAttributes());

        // Viewing live.current returns the current live CashGame
        $this->getJson(route('live.current'))
                ->assertJsonStructure(['success', 'status', 'game'])
                ->assertJson([
                    'success' => true,
                    'status' => 'live',
                    'game' => $user->liveCashGame()->toArray()
                ])
                ->assertOk();
    }

    // Trying to view a Live CashGame when one has not been started is valid but returns empty json.
    public function testCannotViewLiveCashGameIfNotBeenStarted()
    {
        $user = $this->signIn();
        // Don't start CashGame

        $this->getJson(route('live.current'))
                ->assertOk()
                ->assertJsonStructure(['success', 'game'])
                ->assertJson([
                    'success' => true,
                    'game' => []
                ]);

        $this->assertEmpty($user->liveCashGame());
    }

    // User can update live CashGame
    public function testUserCanUpdateTheLiveCashGameDetails()
    {
        $user = $this->signIn();

        // Start a Live CashGame
        $attributes = $this->getLiveCashGameAttributes();
        $this->postJson(route('cash.live.start'), $attributes)->assertOk();

        $updatedAttributes = [
            'start_time' => Carbon::create('-30 mins')->toDateTimeString(),
            'stake_id' => 2,
            'variant_id' => 2,
            'limit_id' => 2,
            'table_size_id' => 2,
            'location' => 'Updated Location',
            'comments' => 'New comments'
        ];

        $this->patchJson(route('cash.live.update'), $updatedAttributes)
                ->assertOk()
                ->assertJsonStructure(['success', 'game'])
                ->assertJson([
                    'success' => true
                ]);

        // Assert we find the updated attributes
        $this->assertDatabaseHas('cash_games', $updatedAttributes);
    }

    // User cannot update a live cash game that does not exist
    public function testCannotUpdateLiveCashGameIfNotBeenStarted()
    {
        $user = $this->signIn();
        // Don't start CashGame

        $attributes = $this->getLiveCashGameAttributes();
        $this->patchJson(route('cash.live.update'), $attributes)
                ->assertStatus(422)
                ->assertJsonStructure(['success', 'message'])
                ->assertJson([
                    'success' => false,
                    'message' => "You have not started a live session."
                ]);

        $this->assertEmpty($user->liveCashGame());
    }

    // User cannot update another user's live cash game
    public function testCannotUpdateAnotherUsersLiveCashGame()
    {
        // Impossible now as we obtain liveCashGame through auth()->user()
        // CashGameTest covers where trying to update by suppliying another user's cash game id.
        $this->assertTrue(true);
    }

    // Data must be valid when updating live cash game
    public function testNonNullableMustBeValidWhenUpdatingLiveCashGame()
    {
        // All of this data is sometimes present, but not nullable and must be valid is supplied.
        $this->signIn();

        $validAttributes = $this->getLiveCashGameAttributes();
        $this->postJson(route('cash.live.start'), $validAttributes)->assertOk();

        // start_time must be valid if supplied
        $attributes = $validAttributes;
        $attributes['start_time'] = 999;
        $this->patchJson(route('cash.live.update'), $attributes)->assertStatus(422);
        $attributes = $validAttributes;
        $attributes['start_time'] = 'Invalid date';
        $this->patchJson(route('cash.live.update'), $attributes)->assertStatus(422);

        // stake must exist in database
        $attributes = $validAttributes;
        $attributes['stake_id'] = 999;
        $this->patchJson(route('cash.live.update'), $attributes)->assertStatus(422);

        // variant must exist in database
        $attributes = $validAttributes;
        $attributes['variant_id'] = 999;
        $this->patchJson(route('cash.live.update'), $attributes)->assertStatus(422);

        // limit must exist in database
        $attributes = $validAttributes;
        $attributes['limit_id'] = 999;
        $this->patchJson(route('cash.live.update'), $attributes)->assertStatus(422);

        // table_size must exist in database
        $attributes = $validAttributes;
        $attributes['table_size_id'] = 999;
        $this->patchJson(route('cash.live.update'), $attributes)->assertStatus(422);

        // location cannot be empty
        $attributes = $validAttributes;
        $attributes['location'] = '';
        $this->patchJson(route('cash.live.update'), $attributes)->assertStatus(422);

        // location must be a string
        $attributes = $validAttributes;
        $attributes['location'] = 999;
        $this->patchJson(route('cash.live.update'), $attributes)->assertStatus(422);
    }

    // Start date cannot be in the future when updating
    public function testStartTimeCannotBeInTheFutureWhenUpdatingLiveCashGame()
    {
        $this->signIn();

        $validAttributes = $this->getLiveCashGameAttributes();
        $this->postJson(route('cash.live.start'), $validAttributes)->assertOk();

        $attributes = $validAttributes;
        $attributes['start_time'] = Carbon::create('+1 second')->toDateTimeString();
        $this->patchJson(route('cash.live.update'), $attributes)->assertStatus(422);
    }

    // Cannot update live cash game with new times which clashes with another cash game
    public function testUpdatingLiveCashGameStartTimeMustNotClashWithExistingCashGame()
    {
        $user = $this->signIn();
        $dateTime = Carbon::create(2020, 06, 01, 12, 0, 0);

        // Add CashGame to database with times 1st June 2020 12:30 - 14:30
        \App\Models\CashGame::factory()->create([
            'user_id' => $user->id,
            'start_time' => $dateTime->toDateTimeString(),
            'end_time' => $dateTime->copy()->addHours(2)->toDateTimeString()
        ]);

        // Start a Live CashGame now which is valid
        $validAttributes = $this->getLiveCashGameAttributes();
        $this->postJson(route('cash.live.start'), $validAttributes)->assertOk();

        $start_time = $dateTime->copy()->addMinutes(30)->toDateTimeString();
        $this->patchJson(route('cash.live.update'), ['start_time' => $start_time])->assertStatus(422);
    }

    // Updating live cash game's times does not clash with itself
    public function testUpdatingLiveCashGameTimesDoesNotClashWithItself()
    {
        // Current Live Cash Game being updated is not included when checking for clashes
        $this->signIn();

        // Start Cash Game an hour ago
        $validAttributes = $this->getLiveCashGameAttributes();
        $validAttributes['start_time'] = Carbon::create('-1 hour')->toDateTimeString();
        $this->postJson(route('cash.live.start'), $validAttributes)->assertOk();

        // Update Live Cash Game start time to 45 minutes ago instead.
        // So that it clashes within 1 hour ago and null but valid to update
        $updateAttributes = [
            'start_time' => Carbon::create('-45 minutes')->toDateTimeString()
        ];
        $this->patchJson(route('cash.live.update'), $updateAttributes)->assertOk();
    }

    // User can just update comments
    public function testUserCanJustUpdateCommentsForLiveSession()
    {
        // Current Live Cash Game being updated is not included when checking for clashes
        $user = $this->signIn();

        // Start Cash Game an hour ago
        $validAttributes = $this->getLiveCashGameAttributes();
        $this->postJson(route('cash.live.start'), $validAttributes)->assertOk();

        $comments = ['comments' => 'New comments'];
        $this->patchJson(route('cash.live.update'), $comments)->assertOk();

        $this->assertEquals($user->liveSession()->comments, 'New comments');
    }

    // User can end a live cash.
    // If no end_time is provided then it defaults to now().
    public function testUserCanEndACashGame()
    {
        $user = $this->signIn();

        $start_time = Carbon::create('-1 hour')->toDateTimeString();
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes(1000, $start_time));
        // Do not provide end_time so default to now
        $this->postJson(route('live.end'), ['amount' => 100])->assertOk();

        $cashGame = $user->cashGames()->first();

        // Assert end_time is now as end_time was not provided.
        $this->assertEquals($cashGame->end_time, Carbon::now()->toDateTimeString());
        // Check CashOut transaction was completed.
        $this->assertInstanceOf(CashOut::class, $cashGame->cashOut);
        // Check the amount of the Cash Out is 100.
        $this->assertEquals(100, $cashGame->cashOut->amount);

    }

    // User can end a live cash game at a specified time
    public function testUserCanEndACashGameAtASuppliedTime()
    {
        $user = $this->signIn();

        // Start a cash game an hour ago
        $start_time = Carbon::create('-1 hour');
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes(1000, $start_time->toDateTimeString()))->assertOk();

        // End the cash game 30 minutes later.
        $end_time = $start_time->copy()->addMinutes(30);
        $this->postJson(route('live.end'), [
                    'end_time' => $end_time->toDateTimeString(),
                    'amount' => 100
                ])
                ->assertOk();

        // Asset end time is correct.  (Need to set micro to 0)
        $cashGame = $user->cashGames()->first();
        $this->assertEquals($cashGame->end_time, $end_time->micro(0));
    }

    // User cannot end a live cash game that does not exist.
    public function testUserCannotEndACashGameWhichDoesntExist()
    {
        $user = $this->signIn();

        // Don't Start CashGame

        // End the cash game
        $this->postJson(route('live.end'), [
                    'amount' => 100
                ])
                ->assertStatus(422)
                ->assertJsonStructure(['success', 'message'])
                ->assertJson([
                    'success' => false,
                    'message' => "You have not started a live session."
                ]);
    }

    // User cannot end a live cash game in the future
    public function testUserCannotEndACashGameInTheFuture()
    {
        $this->signIn();

        // Start one cash game
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes())->assertOk();

        // Try to end the cash game one second in the future
        $this->postJson(route('live.end'), [
                    'end_time' => Carbon::create('+1 second')->toDateTimeString(),
                    'amount' => 100
                ])
                ->assertStatus(422);
    }

    // End time must be valid if provided.
    public function testUserCannotEndACashGameAtAInvalidTime()
    {
        $this->signIn();

        // Start one cash game
        $this->postJson(route('cash.live.start'),$this->getLiveCashGameAttributes())->assertOk();

        // End the cash game trying a string and a number
        $this->postJson(route('live.end'), [
                    'end_time' => 'this is not a date',
                    'amount' => 100
                ])
                ->assertStatus(422);

        $this->postJson(route('live.end'), [
                    'end_time' => 34732,
                    'amount' => 100
                ])
                ->assertStatus(422);
    }

    // User cannot end a live cash game before it's start time
    public function testUserCannotEndACashGameBeforeItsStartTime()
    {
        $time = Carbon::create('-1 hour');

        $this->signIn();

        // Start one cash game at time
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes(1000, $time->toDateTimeString()))->assertOk();

        // End the cash game one second before its start time.
        $this->postJson(route('live.end'), [
                    'end_time' => $time->copy()->subSeconds(1)->toDateTimeString(),
                    'amount' => 100
                ])
                ->assertStatus(422);
    }

    // User can end a live cash game exactly on its start time
    public function testUserCanEndACashGameExactlyOnItsStartTime()
    {
        $time = Carbon::create('-1 hour');

        $this->signIn();

        // Start one cash game at time
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes(1000, $time->toDateTimeString()))->assertOk();

        // End the cash game exactly on its start time.
        $this->postJson(route('live.end'), [
                    'end_time' => $time->toDateTimeString(),
                    'amount' => 100
                ])
                ->assertOk();
    }

    // If no end time is provided then CashOut at current time
    // This is tested under testUserCanEndACashGame

    // If no cash out is provided then it defaults to 0
    public function testCashOutAmountDefaultsToZeroIfNotSupplied()
    {
        $user = $this->signIn();

        // Start one cash game
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes());
        // End cash game without passing any data
        $this->postJson(route('live.end'))->assertOk();

        $cashGame = $user->cashGames()->first();
        // Check CashOut transaction was completed.
        $this->assertInstanceOf(CashOut::class, $cashGame->cashOut);
        // Check the amount of the BuyIn is zero.
        $this->assertEquals(0, $cashGame->cashOut->amount);
    }

    // Cash out amount must be valid
    public function testTheCashOutAmountMustBeValidWhenEndingACashGame()
    {
        $this->signIn();

        // Start one cash game
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes());

        // Must be a number
        $this->postJson(route('live.end'), ['amount' => 'Not a number'])->assertStatus(422);

        // Must be a positive number
        $this->postJson(route('live.end'), ['amount' => -1000])->assertStatus(422);
    }

    // Position and Entries are ignored when ending a cash game
    public function testPositionAndEntriesAreIgnoredWhenEndingACashGame()
    {
        $user = $this->signIn();

        // Start one tournament
        $this->postJson(route('cash.live.start'), $this->getLiveCashGameAttributes());

        $cashOutAttributes = [
            'amount' => 100,
            'position' => 55,
            'entries' => 300
        ];

        $this->postJson(route('live.end'), $cashOutAttributes)->assertOk();
    }
}
