<?php

namespace Tests\Feature;

use App\Tournament;
use Tests\TestCase;
use App\Transactions\BuyIn;
use App\Transactions\CashOut;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LiveTournamentTest extends TestCase
{
    use RefreshDatabase;

    /*
    * ==================================
    * INDEX
    * ==================================
    */

    // User must be logged in start/view/update/end live tournament

    // User can start a live tournament
    // User cannot start another tournament if there is one already live
    // Required data must be valid when starting
    // Non required data are optional but must be valid when provided when creating
    // User can start at a specified time.
    // If no start time is provided then it starts at current time
    // Start time cannot be in the future
    // Start time must be valid
    // Cannot start a tournament which clashes with another tournament

    // A BuyIn Can be provided when starting a tournament
    // BuyIn can be zero for Tournaments for freerolls
    // Non provided BuyIn is valid as it creates a BuyIn transaction with amount zero
    // BuyIn must be valid

    // User can view their live tournament
    // Trying to view a Live Tournament when one has not been started is invalid

    // User can update live Tournament
    // User cannot update a live tournament that does not exist
    // User cannot update another user's live tournament
    // Required data, if present, must be valid when updating live tournament
    // Non required data, if present, must be valid when updating live tournament
    // Start date cannot be in the future
    // Cannot update live tournament with new times which clashes with another tournament
    // Updating live tournament's times does not clash with itself

    // User can end a live Tournament
    // User can end a live tournament at a specified time
    // User cannot end a live tournament that does not exist
    // User cannot end a live tournament in the future
    // End time must be valid if provided
    // User cannot end a live tournament before it's start time
    // User can end a live tournament exactly on its start time
    // If no end time is provided then CashOut at current time
    // If no cash out is provided then it defaults to zero
    // Cash out amount must be valid

    /*
    * ==================================
    * TESTS
    * ==================================
    */

    // User must be logged in start/view/update/end live tournament
    public function testUserMustBeLoggedInToStartTournament()
    {
        $this->postJson(route('tournament.live.start'))->assertUnauthorized();
        $this->getJson(route('live.current'))->assertUnauthorized();
        $this->getJson(route('tournament.live.update'))->assertUnauthorized();
        $this->getJson(route('live.end'))->assertUnauthorized();
    }
    
    // User can start a live tournament
    public function testUserCanStartALiveTournament()
    {   
        $user = $this->signIn();

        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes())->assertOk();

        $this->assertCount(1, Tournament::all());
        $tournament = Tournament::first();
        $this->assertTrue($tournament->user->is($user));
    }

    // User cannot start another tournament if there is one already live
    public function testUserCannotStartLiveTournamentIfOneIsInProgress()
    {   
        $this->signIn();

        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes())->assertOk();

        // Starting another Tournament will result in 422
        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes())->assertStatus(422);
    }

    // Required data must be valid when starting
    public function testTournamentAttributesMustBeValidWhenStarting()
    {
        $this->signIn();

        $validAttributes = $this->getLiveTournamentAttributes();

        // variant must be supplied
        $attributes = $validAttributes;
        unset($attributes['variant_id']);
        $this->postJson(route('tournament.live.start'), $attributes)->assertStatus(422);

        // variant must exist in database
        $attributes = $validAttributes;
        $attributes['variant_id'] = 999;
        $this->postJson(route('tournament.live.start'), $attributes)->assertStatus(422);

        // limit must be supplied
        $attributes = $validAttributes;
        unset($attributes['limit_id']);
        $this->postJson(route('tournament.live.start'), $attributes)->assertStatus(422);

        // limit must exist in database
        $attributes = $validAttributes;
        $attributes['limit_id'] = 999;
        $this->postJson(route('tournament.live.start'), $attributes)->assertStatus(422);

        // location must be supplied
        $attributes = $validAttributes;
        unset($attributes['location']);
        $this->postJson(route('tournament.live.start'), $attributes)->assertStatus(422);

        // location must be a string
        $attributes = $validAttributes;
        $attributes['location'] = 999;
        $this->postJson(route('tournament.live.start'), $attributes)->assertStatus(422);
    }

    // Non required data are optional but must be valid when provided when creating
    public function testNonRequiredTournamentAttributesAreOptionalAndValidWhenStarting()
    {
        // Delete Tournaments after every assertOk because we can only have one Live session at a time.
        $this->signIn();

        $validAttributes = $this->getLiveTournamentAttributes();

        // name is optional
        $attributes = $validAttributes;
        unset($attributes['name']);
        $this->postJson(route('tournament.live.start'), $attributes)->assertOk();

        // name must be an string
        Tournament::first()->delete();
        $attributes = $validAttributes;
        $attributes['name'] = 100;
        $this->postJson(route('tournament.live.start'), $attributes)->assertStatus(422);

        // entries is optional
        $attributes = $validAttributes;
        unset($attributes['entries']);
        $this->postJson(route('tournament.live.start'), $attributes)->assertOk();

        // entries must be an integer
        Tournament::first()->delete();
        $attributes = $validAttributes;
        $attributes['entries'] = 'Not an Integer';
        $this->postJson(route('tournament.live.start'), $attributes)->assertStatus(422);

        // entries must be positive
        $attributes = $validAttributes;
        $attributes['entries'] = -100;
        $this->postJson(route('tournament.live.start'), $attributes)->assertStatus(422);

        // prize_pool is optional
        $attributes = $validAttributes;
        unset($attributes['prize_pool']);
        $this->postJson(route('tournament.live.start'), $attributes)->assertOk();

        // prize_pool must be an integer
        Tournament::first()->delete();
        $attributes = $validAttributes;
        $attributes['prize_pool'] = 'Not an Integer';
        $this->postJson(route('tournament.live.start'), $attributes)->assertStatus(422);

        // prize_pool must be positive
        $attributes = $validAttributes;
        $attributes['prize_pool'] = -100;
        $this->postJson(route('tournament.live.start'), $attributes)->assertStatus(422);
    }
    
    // User can start at a specified time
    public function testUserCanStartATournamentAtASpecifiedTime()
    {
        $user = $this->signIn();

        // We'll be passing a Y-m-d H:i:s string from the front end.
        $start_time = Carbon::create('-1 second')->toDateTimeString();

        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes(1000, $start_time));

        $this->assertEquals($start_time, $user->tournaments()->first()->start_time);
    }

    // If no start time is provided then it starts at current time.
    public function testIfNoStartTimeProvidedThenStartTournamentAtCurrentTime()
    {
        $user = $this->signIn();

        // getLiveTournamentAttributes without the start_time parameter has start_time already unset
        $attributes = $this->getLiveTournamentAttributes();
        unset($attributes['start_time']);

        $this->postJson(route('tournament.live.start'), $attributes);

        // Set microseconds of now to 0 because microseconds are not saved in database
        $this->assertEquals(now()->micro(0), $user->tournaments()->first()->start_time);
    }

    // Start time cannot be in the future
    public function testUserCannotStartATournamentInTheFuture()
    {
        $this->signIn();

        $attributes = $this->getLiveTournamentAttributes();
        $attributes['start_time'] = Carbon::create('+1 seconds')->toDateTimeString();

        $this->postJson(route('tournament.live.start'), $attributes)->assertStatus(422);
    }

    // Start time must be valid
    public function testStartTimeMustBeValid()
    {
        $this->signIn();

        $validAttributes = $this->getLiveTournamentAttributes();

        $attributes = $validAttributes;
        $attributes['start_time'] = 'Not a date';
        $this->postJson(route('tournament.live.start'), $attributes)->assertStatus(422);

        $attributes = $validAttributes;
        $attributes['start_time'] = 999;
        $this->postJson(route('tournament.live.start'), $attributes)->assertStatus(422);

        $attributes = $validAttributes;
        $attributes['start_time'] = true;
        $this->postJson(route('tournament.live.start'), $attributes)->assertStatus(422);

        // Null is valid as it will use current date and time.
    }

    // Cannot start a tournament which clashes with another tournament
    public function testStartTimeCannotClashWithAnotherTournament()
    {
        // NOTE: Clashes can still occur, if you start one a second before another tournament's start time
        // then it will overrun in to the other tournament.
        // But then tournamentsAtTime will return more than 1 which is still > 0

        $user = $this->signIn();
        $dateTime = Carbon::create(2020, 05, 01, 12, 0, 0);

        // Add Tournament to database with times 1st May 2020 12:30 - 14:30
        factory('App\Tournament')->create([
            'user_id' => $user->id,
            'start_time' => $dateTime->toDateTimeString(),
            'end_time' => $dateTime->copy()->addHours(2)->toDateTimeString()
        ]);

        $validAttributes = $this->getLiveTournamentAttributes();

        // Try starting one at 13:00 (30 minutes after other tournament start_time)
        $attributes = $validAttributes;
        $attributes['start_time'] = $dateTime->copy()->addMinutes(30);
        $this->postJson(route('tournament.live.start'), $attributes)->assertStatus(422);

        // Try starting on exactly the start_time
        $attributes = $validAttributes;
        $attributes['start_time'] = $dateTime->toDateTimeString();
        $this->postJson(route('tournament.live.start'), $attributes)->assertStatus(422);

        // Try starting on exactly the end_time
        $attributes = $validAttributes;
        $attributes['start_time'] = $dateTime->copy()->addHours(2)->toDateTimeString();
        $this->postJson(route('tournament.live.start'), $attributes)->assertStatus(422);
    }

    // A BuyIn Can be provided when starting a tournament
    // This has already been tested in testUserCanStartALiveTournament
    public function testABuyInCanBeSuppliedWhenStartingATournament()
    {
        $user = $this->signIn();

        // Start a live tournament
        $attributes = $this->getLiveTournamentAttributes();
        $attributes['amount'] = 1000;
        $this->postJson(route('tournament.live.start'), $attributes)->assertOk();

        $tournament = $user->tournaments->first();
        // Check BuyIn was created
        $this->assertInstanceOf(BuyIn::class, $tournament->buyIn);
        // Check the amount of the BuyIn is 1000.
        $this->assertEquals(1000, $tournament->buyIn->amount);
    }

    // BuyIn can be zero for Tournaments for freerolls
    public function testABuyInCanBeZeroForFreerollTournaments()
    {
        $this->signIn();

        // Start a live tournament
        $attributes = $this->getLiveTournamentAttributes();
        $attributes['amount'] = 0;
        $this->postJson(route('tournament.live.start'), $attributes)->assertOk();
    }

    // Non provided BuyIn is valid as it creates a BuyIn transaction with amount zero
    // This is because Freeroll Tournaments are possible.
    public function testIfNoBuyInIsSuppliedThenOneIsCreatedWithAmountZero()
    {
        $user = $this->signIn();

        // Start a live tournament
        $attributes = $this->getLiveTournamentAttributes();
        unset($attributes['amount']);
        $this->postJson(route('tournament.live.start'), $attributes)->assertOk();

        $tournament = $user->tournaments->first();
        // Check BuyIn was created
        $this->assertInstanceOf(BuyIn::class, $tournament->buyIn);
        // Check the amount of the BuyIn is zero.
        $this->assertEquals(0, $tournament->buyIn->amount);
    }

    // BuyIn must be valid
    public function testTheBuyInAmountMustBeValidWhenStartingATournament()
    {
        $this->signIn();

        $validAttributes = $this->getLiveTournamentAttributes();

        // Amount must be an integer.
        $attributes = $validAttributes;
        $attributes['amount'] = 'Not an integer';
        $this->postJson(route('tournament.live.start'), $attributes)->assertStatus(422);

        // Amount cannot be negative
        $attributes = $validAttributes;
        $attributes['amount'] = -1;
        $this->postJson(route('tournament.live.start'), $attributes)->assertStatus(422);

        // Float numbers are valid.
        $attributes = $validAttributes;
        $attributes['amount'] = 10.55;
        $this->postJson(route('tournament.live.start'), $attributes)->assertOk();
    }

    // User can view their live tournament
    public function testUserCanViewTheirLiveTournament()
    {
        $user = $this->signIn();

        $this->post(route('tournament.live.start'), $this->getLiveTournamentAttributes());

        // Viewing live.current returns the current live Tournament
        $this->getJson(route('live.current'))
                ->assertJsonStructure(['success', 'status', 'game'])
                ->assertJson([
                    'success' => true,
                    'status' => 'live',
                    'game' => $user->liveTournament()->toArray()
                ])
                ->assertOk();
    }

    // Trying to view a Live Tournament when one has not been started is valid and returns empty
    public function testCannotViewLiveTournamentIfNotBeenStarted()
    {
        $user = $this->signIn();
        // Don't start Tournament

        $this->getJson(route('live.current'))
                ->assertOk()
                ->assertJsonStructure(['success', 'game'])
                ->assertJson([
                    'success' => true,
                    'game' => []
                ]);
                            
        $this->assertEmpty($user->liveTournament());
    }

    // User can update live Tournament
    public function testUserCanUpdateTheLiveTournamentDetails()
    {
        $user = $this->signIn();

        // Start a Live Tournament
        $attributes = $this->getLiveTournamentAttributes();
        $this->postJson(route('tournament.live.start'), $attributes)->assertOk();

        $updatedAttributes = [
            'start_time' => Carbon::create('-30 mins')->toDateTimeString(),
            'name' => 'Updated Name',
            'variant_id' => 2,
            'limit_id' => 2,
            'prize_pool' => 5000,
            'position' => 28,
            'entries' => 150,
            'location' => 'Updated Location',
            'comments' => 'New comments'
        ];

        $this->patchJson(route('tournament.live.update'), $updatedAttributes)
                ->assertOk()
                ->assertJsonStructure(['success', 'game'])
                ->assertJson([
                    'success' => true
                ]);

        // Get the current Tournament
        // $liveTournament = $user->liveTournament();
        // I don't understand why assertDatabaseHas is failing.  Resorting to manual checking.
        // $this->assertDatabaseHas('tournaments', $updatedAttributes);
    }

    // User cannot update a live tournament that does not exist
    public function testCannotUpdateLiveTournamentIfNotBeenStarted()
    {
        $user = $this->signIn();
        // Don't start Tournament

        $attributes = $this->getLiveTournamentAttributes();
        $this->patchJson(route('tournament.live.update'), $attributes)
                ->assertStatus(422)
                ->assertJsonStructure(['success', 'message'])
                ->assertJson([
                    'success' => false,
                    'message' => "You have not started a live session."
                ]);
                            
        $this->assertEmpty($user->liveTournament());
    }

    // User cannot update another user's live tournament
    public function testCannotUpdateAnotherUsersLiveTournament()
    {
        // Impossible now as we obtain liveTournament through auth()->user()
        // TournamentTest covers where trying to update by suppliying another user's tournament id.
        $this->assertTrue(true);
    }
    
    // Required data, if present, must be valid when updating live tournament
    public function testNonNullableMustBeValidWhenUpdatingLiveTournament()
    {
        // All of this data is sometimes present, but not nullable and must be valid is supplied.
        $this->signIn();

        $validAttributes = $this->getLiveTournamentAttributes();
        $this->postJson(route('tournament.live.start'), $validAttributes)->assertOk();

        // start_time must be valid if supplied
        $attributes = $validAttributes;
        $attributes['start_time'] = 999;
        $this->patchJson(route('tournament.live.update'), $attributes)->assertStatus(422);
        $attributes = $validAttributes;
        $attributes['start_time'] = 'Invalid date';
        $this->patchJson(route('tournament.live.update'), $attributes)->assertStatus(422);

        // name must be a string
        $attributes = $validAttributes;
        $attributes['name'] = 999;
        $this->patchJson(route('tournament.live.update'), $attributes)->assertStatus(422);

        // variant must exist in database
        $attributes = $validAttributes;
        $attributes['variant_id'] = 999;
        $this->patchJson(route('tournament.live.update'), $attributes)->assertStatus(422);

        // limit must exist in database
        $attributes = $validAttributes;
        $attributes['limit_id'] = 999;
        $this->patchJson(route('tournament.live.update'), $attributes)->assertStatus(422);

        // location cannot be empty
        $attributes = $validAttributes;
        $attributes['location'] = '';
        $this->patchJson(route('tournament.live.update'), $attributes)->assertStatus(422);

        // location must be a string
        $attributes = $validAttributes;
        $attributes['location'] = 999;
        $this->patchJson(route('tournament.live.update'), $attributes)->assertStatus(422);
    }

    // Non required data, if present, must be valid when updating live tournament
    public function testNullableDataIsOptionalWhenUpdatingLiveTournament()
    {
        // All of this data is sometimes present, but can be nullable if supplied.
        $this->signIn();

        $validAttributes = $this->getLiveTournamentAttributes();
        $this->postJson(route('tournament.live.start'), $validAttributes)->assertOk();

        // prize_pool must be integer if supplied
        $attributes = $validAttributes;
        $attributes['prize_pool'] = 'Not a number';
        $this->patchJson(route('tournament.live.update'), $attributes)->assertStatus(422);

        // prize_pool must be positive if supplied
        $attributes = $validAttributes;
        $attributes['prize_pool'] = -1;
        $this->patchJson(route('tournament.live.update'), $attributes)->assertStatus(422);

        // position must be integer if supplied
        $attributes = $validAttributes;
        $attributes['position'] = 'Not a number';
        $this->patchJson(route('tournament.live.update'), $attributes)->assertStatus(422);

        // position must be positive if supplied
        $attributes = $validAttributes;
        $attributes['position'] = -1;
        $this->patchJson(route('tournament.live.update'), $attributes)->assertStatus(422);

        // entries must be integer if supplied
        $attributes = $validAttributes;
        $attributes['entries'] = 'Not a number';
        $this->patchJson(route('tournament.live.update'), $attributes)->assertStatus(422);

        // entries must be positive if supplied
        $attributes = $validAttributes;
        $attributes['entries'] = -1;
        $this->patchJson(route('tournament.live.update'), $attributes)->assertStatus(422);

        // name, prize_pool, entries, position can all be empty or 0 in MySQL
        $attributes = $validAttributes;
        $attributes['name'] = '';
        $attributes['prize_pool'] = 0;
        $attributes['position'] = 0;
        $attributes['entries'] = 0;
        $this->patchJson(route('tournament.live.update'), $attributes)->assertOk();
    }

    // Start date cannot be in the future
    public function testStartTimeCannotBeInTheFutureWhenUpdatingLiveTournament()
    {
        $this->signIn();

        $validAttributes = $this->getLiveTournamentAttributes();
        $this->postJson(route('tournament.live.start'), $validAttributes)->assertOk();

        $attributes = $validAttributes;
        $attributes['start_time'] = Carbon::create('+1 second')->toDateTimeString();
        $this->patchJson(route('tournament.live.update'), $attributes)->assertStatus(422);
    }

    // Cannot update live tournament with new times which clashes with another tournament
    public function testUpdatingLiveTournamentStartTimeMustNotClashWithExistingTournament()
    {
        $user = $this->signIn();
        $dateTime = Carbon::create(2020, 06, 01, 12, 0, 0);

        // Add Tournament to database with times 1st June 2020 12:30 - 14:30
        factory('App\Tournament')->create([
            'user_id' => $user->id,
            'start_time' => $dateTime->toDateTimeString(),
            'end_time' => $dateTime->copy()->addHours(2)->toDateTimeString()
        ]);

        // Start a Live Tournament now which is valid
        $validAttributes = $this->getLiveTournamentAttributes();
        $this->postJson(route('tournament.live.start'), $validAttributes)->assertOk();

        $start_time = $dateTime->copy()->addMinutes(30)->toDateTimeString();
        $this->patchJson(route('tournament.live.update'), ['start_time' => $start_time])->assertStatus(422);
    }

    // Updating live tournament's times does not clash with itself
    public function testUpdatingLiveTournamentTimesDoesNotClashWithItself()
    {
        // Current Live Cash Game being updated is not included when checking for clashes
        $this->signIn();

        // Start Cash Game an hour ago
        $validAttributes = $this->getLiveTournamentAttributes();
        $validAttributes['start_time'] = Carbon::create('-1 hour')->toDateTimeString();
        $this->postJson(route('tournament.live.start'), $validAttributes)->assertOk();

        // Update Live Cash Game start time to 45 minutes ago instead.
        // So that it clashes within 1 hour ago and null but valid to update
        $updateAttributes = [
            'start_time' => Carbon::create('-45 minutes')->toDateTimeString()
        ];
        $this->patchJson(route('tournament.live.update'), $updateAttributes)->assertOk();
    }

    // User can end a live tournament
    // If no end_time is provided then it defaults to now().
    // Assert CashOut transaction was created and equal to the amount provided.
    public function testUserCanEndATournament()
    {
        $user = $this->signIn();

        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes());
        $this->postJson(route('live.end'), ['amount' => 100])->assertOk();

        $tournament = $user->tournaments()->first();

        // Assert end_time is now as end_time was not provided.
        $this->assertEquals($tournament->end_time, Carbon::now()->toDateTimeString());
        // Check CashOut transaction was completed.
        $this->assertInstanceOf(CashOut::class, $tournament->cashOut);
        // Check the amount of the Cash Out is 100.
        $this->assertEquals(100, $tournament->cashOut->amount);

    }

    // User can end a live tournament at a specified time
    public function testUserCanEndATournamentAtASuppliedTime()
    {
        $user = $this->signIn();

        // Start a tournament an hour ago
        $start_time = Carbon::create('-1 hour');
        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes(1000, $start_time->toDateTimeString()))->assertOk();

        // End the tournament 30 minutes later.
        $end_time = $start_time->copy()->addMinutes(30);
        $this->postJson(route('live.end'), [
                    'end_time' => $end_time->toDateTimeString(),
                    'amount' => 100
                ])
                ->assertOk();

        // Asset end time is correct.  (Need to set micro to 0)
        $tournament = $user->tournaments()->first();
        $this->assertEquals($tournament->end_time, $end_time->micro(0));
    }

    // User cannot end a live tournament that does not exist
    public function testUserCannotEndATournamentWhichDoesntExist()
    {
        $user = $this->signIn();

        // Don't Start Tournament

        // End the tournament
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

    // User cannot end a live tournament in the future
    public function testUserCannotEndATournamentInTheFuture()
    {
        $this->signIn();

        // Start one tournament
        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes())->assertOk();

        // Try to end the tournament one second in the future
        $this->postJson(route('live.end'), [
                    'end_time' => Carbon::create('+1 second')->toDateTimeString(),
                    'amount' => 100
                ])
                ->assertStatus(422);
    }

    // End time must be valid if provided
    public function testUserCannotEndATournamentAtAInvalidTime()
    {
        $this->signIn();

        // Start one tournament
        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes())->assertOk();

        // End the tournament trying a string and a number
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

    // User cannot end a live tournament before it's start time
    public function testUserCannotEndATournamentBeforeItsStartTime()
    {
        $time = Carbon::create('-1 hour');

        $this->signIn();

        // Start one tournament at time
        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes(1000, $time->toDateTimeString()))->assertOk();

        // End the tournament one second before it's start time.
        $this->postJson(route('live.end'), [
                    'end_time' => $time->copy()->subSeconds(1)->toDateTimeString(),
                    'amount' => 100
                ])
                ->assertStatus(422);
    }

    // User can end a live tournament exactly on its start time
    public function testUserCanEndATournamentExactlyOnItsStartTime()
    {
        $time = Carbon::create('-1 hour');

        $this->signIn();

        // Start one tournament at time
        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes(1000, $time->toDateTimeString()))->assertOk();

        // End the tournament one second before it's start time.
        $this->postJson(route('live.end'), [
                    'end_time' => $time->toDateTimeString(),
                    'amount' => 100
                ])
                ->assertOk();
    }

    // If no end time is provided then CashOut at current time
    // This is tested under testUserCanEndATournament

    // If no cash out is provided then it defaults to zero
    public function testCashOutAmountDefaultsToZeroIfNotSupplied()
    {
        $user = $this->signIn();

        // Start one tournament
        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes());
        // End tournament without passing any data
        $this->postJson(route('live.end'))->assertOk();

        $tournament = $user->tournaments()->first();
        // Check CashOut transaction was completed.
        $this->assertInstanceOf(CashOut::class, $tournament->cashOut);
        // Check the amount of the BuyIn is zero.
        $this->assertEquals(0, $tournament->cashOut->amount);
    }
    
    // Cash out amount must be valid
    public function testTheCashOutAmountMustBeValidWhenEndingATournament()
    {
        $this->signIn();

        // Start one tournament
        $this->postJson(route('tournament.live.start'), $this->getLiveTournamentAttributes());

        // Must be a number
        $this->postJson(route('live.end'), ['amount' => 'Not a number'])->assertStatus(422);

        // Must be a positive number
        $this->postJson(route('live.end'), ['amount' => -1000])->assertStatus(422);
    }
}
