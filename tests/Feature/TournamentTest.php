<?php

namespace Tests\Feature;

use App\Tournament;
use Tests\TestCase;
use App\Transactions\BuyIn;
use App\Transactions\CashOut;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TournamentTest extends TestCase
{
    use RefreshDatabase;

    /*
    * ==================================
    * INDEX
    * ==================================
    */

    // User must be logged in create/view/view all/update/delete tournament

    // User can create a tournament
    // Required data must be valid when creating when creating
    // Non required data must be valid if present when creating
    // Start time cannot be in the future when creating
    // End time cannot be in the future when creating
    // End time cannot be before start time when creating
    // Start time and end time can be the same when creating
    // Start time and end time can be exactly now when creating
    // Cannot add tournament which clashes with another tournament
    // User can add a completed tournament with start time before a current live tournament
    // User cannot add a completed tournament if start_time clashes with a live tournament

    // BuyIn must be supplied
    // Cannot add multiple BuyIns
    // BuyIn CAN be zero for freerolls
    // BuyIn must be valid.

    // Expenses can be supplied when creating a tournament
    // Expenses are optional
    // Expenses must be valid
    // Rebuys can be supplied when creating a tournament
    // Rebuys are optional
    // Rebuys must be valid
    // AddOns can be supplied when creating a tournament
    // AddOns are optional
    // AddOns must be valid

    // Cash Out can be provided when creating a tournament
    // If CashOut not supplied then a CashOut is created with amount zero
    // Cash Out amount can be zero
    // Cash Out must be valid

    // User can view all their tournaments
    // User can view their tournament
    // Tournament must exist when viewing
    // User cannot view another user's tournament

    // User can update tournament
    // tournament must exist when updating
    // User cannot update another user's tournament
    // Data must be valid when updating tournament
    // Start time cannot be in the future when updating
    // End time cannot be in the future when updating
    // Providing both start and end time, end time cannot be before start time
    // Start time and end time can be the same when updating
    // Start time and end time can be exactly now when updating
    // Only providing end time, it cannot be before tournament's start time
    // Only providing start time, it cannot be after tournament's end time
    // Cannot update tournament with new start time that clashes with another tournament
    // Updating tournament's times does not clash with itself

    // User can delete their tournament
    // Tournament must exist when deleting
    // User cannot delete another user's tournament

    /*
    * ==================================
    * TESTS
    * ==================================
    */


    // User must be logged in create/view/view all/update/delete tournament
    public function testUserMustBeLoggedInToCreateTournament()
    {
        $tournament = \App\Tournament::factory()->create();
        $validAttributes = $this->getTournamentAttributes();

        $this->getJson(route('tournament.index'))->assertUnauthorized();
        $this->postJson(route('tournament.create'), $validAttributes)->assertUnauthorized();
        $this->getJson(route('tournament.view', ['tournament' => $tournament->id]))->assertUnauthorized();
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $validAttributes)->assertUnauthorized();
        $this->deleteJson(route('tournament.delete', ['tournament' => $tournament->id]))->assertUnauthorized();
    }

    // User can create a tournament
    public function testUserCanAddACompletedTournament()
    {
        $user = $this->signIn();

        $attributes = $this->getTournamentAttributes();

        $this->postJson(route('tournament.create'), $attributes)->assertOk();

        // Get the basic tournament attributes
        $tournament_attributes = $attributes;
        unset($tournament_attributes['buy_in'], $tournament_attributes['expenses'], $tournament_attributes['cash_out'], $tournament_attributes['rebuys'], $tournament_attributes['add_ons']);

        $tournament = $user->tournaments()->first();

        $this->assertCount(1, $user->tournaments);
        // assertDatabase does not work with tournaments for some reason
        // $this->assertDatabaseHas('tournaments', $tournament_attributes);
        $this->assertInstanceOf(BuyIn::class, $tournament->buyIn);
        $this->assertCount(2, $tournament->expenses);
        $this->assertInstanceOf(CashOut::class, $tournament->cashOut);
    }

    // Required data must be valid when creating when creating
    // Data must be valid when creating
    public function testRequiredDataMustBePresentAndValidWhenAddingCompletedTournament()
    {
        $this->signIn();

        // Valid attributes.  Will change each one before testing.
        $validAttributes = $this->getTournamentAttributes();

        // start_time must be supplied
        $attributes = $validAttributes;
        unset($attributes['start_time']);
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // start_time be a date
        $attributes = $validAttributes;
        $attributes['start_time'] = 999; // Number
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);
        $attributes = $validAttributes;
        $attributes['start_time'] = 'Not a date'; // Non date string
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // end_time must be supplied
        $attributes = $validAttributes;
        unset($attributes['end_time']);
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // end_time be a date
        $attributes = $validAttributes;
        $attributes['end_time'] = 999; // Number
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);
        $attributes = $validAttributes;
        $attributes['end_time'] = 'Not a date'; // Non date string
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // variant must be supplied
        $attributes = $validAttributes;
        unset($attributes['variant_id']);
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // variant must exist in database
        $attributes = $validAttributes;
        $attributes['variant_id'] = 999;
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // limit must be supplied
        $attributes = $validAttributes;
        unset($attributes['limit_id']);
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // limit must exist in database
        $attributes = $validAttributes;
        $attributes['limit_id'] = 999;
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // location must be supplied
        $attributes = $validAttributes;
        unset($attributes['location']);
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // location must be a string
        $attributes = $validAttributes;
        $attributes['location'] = 999;
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);
    }

    // Non required data must be valid if present when creating
    public function testNonRequiredTournamentAttributesAreOptionalAndValidWhenCreating()
    {
        // Delete Tournaments after every assertOk because we can only have one Live session at a time.
        $this->signIn();

        $validAttributes = $this->getTournamentAttributes();

        // name is optional
        $attributes = $validAttributes;
        unset($attributes['name']);
        $this->postJson(route('tournament.create'), $attributes)->assertOk();

        // name is nullable
        Tournament::first()->delete();
        $attributes = $validAttributes;
        $attributes['name'] = '';
        $this->postJson(route('tournament.create'), $attributes)->assertOk();

        // name must be string
        Tournament::first()->delete();
        $attributes = $validAttributes;
        $attributes['name'] = 100;
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // entries is optional
        $attributes = $validAttributes;
        unset($attributes['entries']);
        $this->postJson(route('tournament.create'), $attributes)->assertOk();

        // entries must be an integer
        Tournament::first()->delete();
        $attributes = $validAttributes;
        $attributes['entries'] = 'Not an Integer';
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // entries must be positive
        $attributes = $validAttributes;
        $attributes['entries'] = -100;
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // prize_pool is optional
        $attributes = $validAttributes;
        unset($attributes['prize_pool']);
        $this->postJson(route('tournament.create'), $attributes)->assertOk();

        // prize_pool must be an integer
        Tournament::first()->delete();
        $attributes = $validAttributes;
        $attributes['prize_pool'] = 'Not an Integer';
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // prize_pool must be positive
        $attributes = $validAttributes;
        $attributes['prize_pool'] = -100;
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);
    }

    // Start time cannot be in the future when creating
    public function testStartTimeCannotBeInTheFutureWhenCreating()
    {
        $this->signIn();

        $attributes = $this->getTournamentAttributes();

        $attributes['start_time'] = Carbon::create('+1 seconds')->toDateTimeString();
        $attributes['end_time'] = Carbon::create('-10 minutes')->toDateTimeString();

        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);
    }

    // End time cannot be in the future when creating
    public function testEndTimeCannotBeInTheFutureWhenCreating()
    {
        $this->signIn();

        $attributes = $this->getTournamentAttributes();

        $attributes['start_time'] = Carbon::create('-10 minutes')->toDateTimeString();
        $attributes['end_time'] = Carbon::create('+1 seconds')->toDateTimeString();

        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);
    }

    // End time cannot be before start time when creating
    public function testEndTimeCannotBeBeforeStartTimeWhenCreating()
    {
        $this->signIn();

        $attributes = $this->getTournamentAttributes();

        // Both times are in the past but end time is one second before start time
        $time = Carbon::create('-1 hour');
        $attributes['start_time'] = $time->toDateTimeString();
        $attributes['end_time'] = $time->copy()->subSeconds(1)->toDateTimeString();

        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);
    }

    // Start time and end time can be the same when creating
    public function testStartTimeAndEndTimeCanTheSameWhenCreating()
    {
        $this->signIn();

        $attributes = $this->getTournamentAttributes();

        // Start time and end time are the same
        $attributes['start_time'] = Carbon::create('-10 minutes')->toDateTimeString();
        $attributes['end_time'] = Carbon::create('-10 minutes')->toDateTimeString();

        $this->postJson(route('tournament.create'), $attributes)->assertOk();
    }

    // Start time and end time can be exactly now when creating
    public function testStartTimeAndEndTimeCanBeExactlyNowWhenCreating()
    {
        $this->signIn();

        $attributes = $this->getTournamentAttributes();

        // Start time and end time are the same and both set to now
        $attributes['start_time'] = Carbon::now()->toDateTimeString();
        $attributes['end_time'] = Carbon::now()->toDateTimeString();

        $this->postJson(route('tournament.create'), $attributes)->assertOk();
    }

    // Cannot add tournament which clashes with another tournament
    public function testCannotCreateTournamentIfOneExistsBetweenStartAndEndTimes()
    {
        $user = $this->signIn();

        // Create a Tournament with start time 1st May 2020 12:00 - 13:00
        $start_time = Carbon::create(2020, 05, 01, 12, 0, 0);
        $end_time = Carbon::create(2020, 05, 01, 13, 0, 0);

        \App\Tournament::factory()->create([
            'user_id' => $user->id,
            'start_time' => $start_time->toDateTimeString(),
            'end_time' => $end_time->toDateTimeString(),
        ]);

        $attributes = $this->getTournamentAttributes();

        // Creating with start time equal to other start time
        $attributes['start_time'] = $start_time->copy()->toDateTimeString();
        $attributes['end_time'] = $end_time->copy()->addSeconds(1)->toDateTimeString();
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // Creating with start time in between to other start time and end time
        $attributes['start_time'] = $start_time->copy()->addSeconds(1)->toDateTimeString();
        $attributes['end_time'] = $end_time->copy()->addSeconds(1)->toDateTimeString();
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // Creating with start time in equal to other end time
        $attributes['start_time'] = $end_time->copy()->toDateTimeString();
        $attributes['end_time'] = $end_time->copy()->addSeconds(1)->toDateTimeString();
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // NOTE:
        // Creating a tournament with valid start time but clashing end_time is valid
        // End time is equal to other start time
        // TODO: Do I reject complete overlap?  Need to modify user->tournamentsAtTime(start_time) to accept and check end time too
        $attributes['start_time'] = $start_time->copy()->subSeconds(1)->toDateTimeString();
        $attributes['end_time'] = $start_time->copy()->toDateTimeString();
        $this->postJson(route('tournament.create'), $attributes)->assertOk();
    }

    // User can add a completed tournament with start time before a current live tournament
    public function testUserCanAddCompletedTournamentBeforeALiveTournament()
    {
        $this->signIn();

        // Start a Live Tournament 10 minutes ago.
        $time = Carbon::create('-10 minutes');
        $liveAttributes = $this->getLiveTournamentAttributes();
        $liveAttributes['start_time'] = $time->toDateTimeString();
        $this->post(route('tournament.live.start'), $liveAttributes);

        // Create a completed Tournament with start_time 1 hour before and end_time 30 minutes before.
        $validAttributes = $this->getTournamentAttributes();
        $attributes = $validAttributes;
        $attributes['start_time'] = $time->copy()->subMinutes(60)->toDateTimeString();
        $attributes['end_time'] = $time->copy()->subMinutes(30)->toDateTimeString();

        $this->postJson(route('tournament.create'), $attributes)->assertOk();

        // NOTE: Currently clashes are still possible because if you create a completed tournament
        // with start time one second before live tournament but end time is during live session.
        // Start time is 10 minutes and 1 second ago
        // End time is 9 minutes ago.
        $attributes = $validAttributes;
        $attributes['start_time'] = $time->copy()->subSeconds(1)->toDateTimeString();
        $attributes['end_time'] = $time->copy()->addMinutes(1)->toDateTimeString();

        $this->postJson(route('tournament.create'), $attributes)->assertOk();
    }

    // User cannot add a completed tournament if start_time clashes with a live tournament
    public function testUserCannotAddCompletedTournamentIfStartTimeClashesALiveTournament()
    {
        $this->signIn();

        // Start a Live Tournament 10 minutes ago.
        $time = Carbon::create('-10 mins');
        $liveAttributes = $this->getLiveTournamentAttributes();
        $liveAttributes['start_time'] = $time->toDateTimeString();
        $this->post(route('tournament.live.start'), $liveAttributes);

        // Create a completed tournament with start time 1 second after live tournament
        $attributes = $this->getTournamentAttributes();
        $attributes['start_time'] = $time->copy()->addSeconds(1)->toDateTimeString();
        $attributes['end_time'] = $time->copy()->addSeconds(2)->toDateTimeString();

        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);
    }

    // BuyIn must be supplied
    public function testBuyInsMustBeSuppliedWhenAddingACompletedTournament()
    {
        $user = $this->signIn();

        $attributes = $this->getTournamentAttributes();

        $attributes['buy_in'] = [];
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        unset($attributes['buy_in']);
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
    }

    // Cannot add multiple BuyIns to Tournaments
    public function testTournamentsCanOnlyHaveOneBuyIn()
    {
        $user = $this->signIn();

        $attributes = $this->getTournamentAttributes();
        $attributes['buy_in'] = [
            ['amount' => 100, 'currency' => 'GBP'],
            ['amount' => 50, 'currency' => 'GBP'],
            ['amount' => 75, 'currency' => 'GBP'],
        ];

        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);
    }

    // BuyIn CAN be zero for freerolls
    public function testBuyInAmountsCanBeZeroForFreerolls()
    {
        $user = $this->signIn();

        $attributes = $this->getTournamentAttributes();
        $attributes['buy_in'] = ['amount' => 0, 'currency' => 'GBP'];

        $this->postJson(route('tournament.create'), $attributes)->assertOk();

        $this->assertInstanceOf(BuyIn::class, $user->tournaments->first()->buyIn);
        $this->assertEquals(0, $user->tournaments->first()->buyIn->amount);
    }

    // BuyIn must be valid.
    public function testBuyInsMustBeValid()
    {
        $this->signIn();

        $attributes = $this->getTournamentAttributes();

        // BuyIn amounts must be an integer
        $attributes['buy_in'] = ['amount' => 'Not a number', 'currency' => 'GBP'];
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // BuyIn amounts cannot be negative
        $attributes['buy_in'] = ['amount' => -100, 'currency' => 'GBP'];
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);
    }

    // Expenses can be supplied when creating a tournament
    public function testExpensesCanBeSuppliedWhenCreatingTournament()
    {
        $user = $this->signIn();

        // Add two expenses.
        $attributes = $this->getTournamentAttributes();
        $attributes['expenses'] = [
            ['amount' => 400, 'currency' => 'GBP'],
            ['amount' => 750, 'currency' => 'GBP', 'comments' => 'Tips'],
        ];
        $this->postJson(route('tournament.create'), $attributes)->assertOk();

        $this->assertCount(2, $user->tournaments->first()->expenses);
        $this->assertEquals(400, $user->tournaments->first()->expenses[0]->amount);
        $this->assertEquals(750, $user->tournaments->first()->expenses[1]->amount);
    }

    // Expenses are optional
    public function testExpensesAreOptionalWhenCreatingTournament()
    {
        $user = $this->signIn();

        $attributes = $this->getTournamentAttributes();
        unset($attributes['expenses']);
        $this->postJson(route('tournament.create'), $attributes)->assertOk();

        $this->assertCount(0, $user->tournaments->first()->expenses);
    }

    // Expenses must be valid
    public function testExpensesMustBeValidIfAdding()
    {
        $this->signIn();

        $attributes = $this->getTournamentAttributes();

        // Expense amount must be an integer
        $attributes['expenses'] = [
            ['amount' => 'Not a number', 'currency' => 'GBP']
        ];
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // Expense amount must be postive
        $attributes['expenses'] = [
            ['amount' => -100, 'currency' => 'GBP']
        ];
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // Expense amount cannot be zero
        $attributes['expenses'] = [
            ['amount' => 0, 'currency' => 'GBP']
        ];
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // Expense amount must be present if adding comments
        $attributes['expenses'] = [
            ['comments' => 'Expenses comment']
        ];
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);
    }

    // Rebuys can be supplied when creating a tournament
    public function testRebuysCanBeSuppliedWhenCreatingTournament()
    {
        $user = $this->signIn();

        // Add two expenses.
        $attributes = $this->getTournamentAttributes();
        $attributes['rebuys'] = [
            ['amount' => 100, 'currency' => 'GBP'],
            ['amount' => 200, 'currency' => 'GBP'],
        ];
        $this->postJson(route('tournament.create'), $attributes)->assertOk();

        $this->assertCount(2, $user->tournaments->first()->rebuys);
        $this->assertEquals(100, $user->tournaments->first()->rebuys[0]->amount);
        $this->assertEquals(200, $user->tournaments->first()->rebuys[1]->amount);
    }

    // Rebuys are optional
    public function testRebuysAreOptionalWhenCreatingTournament()
    {
        $user = $this->signIn();

        $attributes = $this->getTournamentAttributes();
        unset($attributes['rebuys']);
        $this->postJson(route('tournament.create'), $attributes)->assertOk();

        $this->assertCount(0, $user->tournaments->first()->rebuys);
    }

    // Rebuys must be valid
    public function testRebuysMustBeValidIfAdding()
    {
        $this->signIn();

        $attributes = $this->getTournamentAttributes();

        // Rebuy amount must be an integer
        $attributes['rebuys'] = [
            ['amount' => 'Not a number', 'currency' => 'GBP']
        ];
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // Rebuy amount must be postive
        $attributes['rebuys'] = [
            ['amount' => -100, 'currency' => 'GBP']
        ];
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // Rebuy amount cannot be zero
        $attributes['rebuys'] = [
            ['amount' => 0, 'currency' => 'GBP']
        ];
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);
    }

    // AddOns can be supplied when creating a tournament
    public function testAddOnsCanBeSuppliedWhenCreatingTournament()
    {
        $user = $this->signIn();

        // Add two expenses.
        $attributes = $this->getTournamentAttributes();
        $attributes['add_ons'] = [
            ['amount' => 250, 'currency' => 'GBP'],
            ['amount' => 500, 'currency' => 'GBP'],
        ];
        $this->postJson(route('tournament.create'), $attributes)->assertOk();

        $this->assertCount(2, $user->tournaments->first()->addOns);
        $this->assertEquals(250, $user->tournaments->first()->addOns[0]->amount);
        $this->assertEquals(500, $user->tournaments->first()->addOns[1]->amount);
    }

    // AddOns are optional
    public function testAddOnsAreOptionalWhenCreatingTournament()
    {
        $user = $this->signIn();

        $attributes = $this->getTournamentAttributes();
        unset($attributes['add_ons']);
        $this->postJson(route('tournament.create'), $attributes)->assertOk();

        $this->assertCount(0, $user->tournaments->first()->addOns);
    }

    // AddOns must be valid
    public function testAddOnsMustBeValidIfAdding()
    {
        $this->signIn();

        $attributes = $this->getTournamentAttributes();

        // Rebuy amount must be an integer
        $attributes['add_ons'] = [
            ['amount' => 'Not a number', 'currency' => 'GBP']
        ];
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // Rebuy amount must be postive
        $attributes['add_ons'] = [
            ['amount' => -100, 'currency' => 'GBP']
        ];
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // Rebuy amount cannot be zero
        $attributes['add_ons'] = [
            ['amount' => 0, 'currency' => 'GBP']
        ];
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);
    }

    // Cash Out can be provided when creating a tournament
    public function testCashOutCanBeSuppliedWhenCreatingTournament()
    {
        $user = $this->signIn();

        $attributes = $this->getTournamentAttributes();
        $attributes['cash_out'] = ['amount' => 555, 'currency' => 'GBP'];
        $this->postJson(route('tournament.create'), $attributes)->assertOk();

        $this->assertEquals(555, $user->tournaments->first()->cashOut->amount);
    }

    // If CashOut not supplied then a CashOut is created with amount zero
    public function testNotSupplyingCashOutDefaultsToZero()
    {
        $user = $this->signIn();

        $attributes = $this->getTournamentAttributes();
        unset($attributes['cash_out']);
        $this->postJson(route('tournament.create'), $attributes)->assertOk();

        // Assert CashOut Transaction was created and amount equal to 0
        $this->assertInstanceOf(CashOut::class, $user->tournaments->first()->cashOut);
        $this->assertEquals(0, $user->tournaments->first()->cashOut->amount);
    }

    // Cash Out amount can be zero
    public function testCashOutAmountCanBeZero()
    {
        $user = $this->signIn();

        $attributes = $this->getTournamentAttributes();
        $attributes['cash_out'] = ['amount' => 0, 'currency' => 'GBP'];
        $this->postJson(route('tournament.create'), $attributes)->assertOk();

        $this->assertEquals(0, $user->tournaments->first()->cashOut->amount);
    }

    // Cash Out must be valid
    public function testCashOutMustBeValidIfAdding()
    {
        $this->signIn();

        $attributes = $this->getTournamentAttributes();

        // CashOut amount must be a number
        $attributes['cash_out'] = ['amount' => 'Not a number', 'currency' => 'GBP'];
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // CashOut amount must be positive
        $attributes['cash_out'] = ['amount' => -100, 'currency' => 'GBP'];
        $this->postJson(route('tournament.create'), $attributes)->assertStatus(422);

        // CashOut can be an empty array as it default to zero
        $attributes['cash_out'] = [];
        $this->postJson(route('tournament.create'), $attributes)->assertOk();
    }

    // User can view all their tournaments
    public function testUserCanGetAllTheirTournamentsAsJson()
    {
        $user = $this->signIn();

        // Assert response tournaments is empty if no tournaments exist
        $response = $this->getJson(route('tournament.index'))->assertOk()->assertJsonStructure(['success', 'tournaments']);
        $this->assertEmpty($response['tournaments']);

        // Create three tournaments
        \App\Tournament::factory()->count(3)->create(['user_id' => $user->id]);

        // Assert response tournaments returns two tournaments
        $response = $this->getJson(route('tournament.index'))->assertOk()->assertJsonStructure(['success', 'tournaments']);
        $this->assertCount(3, $response['tournaments']);
    }

    // User can view their tournament
    public function testUserCanViewAValidTournament()
    {
        $user = $this->signIn();
        $tournament = \App\Tournament::factory()->create(['user_id' => $user->id]);

        // Assert Not Found if supply incorrect tournament id
        $this->getJson(route('tournament.view', ['tournament' => $tournament->id]))
                ->assertOk()
                ->assertJsonStructure(['success', 'tournament'])
                ->assertJson([
                    'success' => true,
                    'tournament' => $tournament->fresh()->toArray()
                ]);
    }

    // Tournament must exist when viewing
    public function testAssertNotFoundIfViewingInvalidTournament()
    {
        $this->signIn();

        // Assert Not Found if supply incorrect tournament id
        $this->getJson(route('tournament.view', ['tournament' => 99]))->assertNotFound();
    }

    // User cannot view another user's tournament
    public function testUserCannotViewAnotherUsersTournament()
    {
        // Create a Tournament which also creates a user for it
        $tournament = \App\Tournament::factory()->create();

        // Create a new user and sign in
        $this->signIn();

        // Assert Forbidden if tournament does not belong to current user
        $this->getJson(route('tournament.view', ['tournament' => $tournament->id]))->assertForbidden();
    }

    // User can update tournament
    public function testUserCanUpdateTournamentDetails()
    {
        $user = $this->signIn();
        $tournament = \App\Tournament::factory()->create(['user_id' => $user->id]);

        $attributes = [
            'start_time' => Carbon::create('-1 hour')->toDateTimeString(),
            'end_time' => Carbon::create('-30 minutes')->toDateTimeString(),
            'variant_id' => 1,
            'limit_id' => 1,
            'prize_pool' => 1000,
            'position' => 18,
            'entries' => 143,
            'name' => 'My favourite tournament',
            'location' => 'Updated Location',
            'comments' => 'Updated Comments',
        ];

        // Make sure new attributes are missing
        // assertDatabaseMissing not working with tournaments. Need to look in to it
        // $this->assertDatabaseMissing('tournaments', $attributes);

        // Update and check database for updated attributes
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)->assertOk();

        // assertDatabaseHas not working with tournaments. Need to look in to it
        // $this->assertDatabaseHas('tournaments', $attributes);
    }

    // Tournament must exist when updating
    public function testTournamentMustExistWhenUpdating()
    {
        $this->signIn();

        $this->patchJson(route('tournament.update', ['tournament' => 1]))->assertNotFound();
    }

    // User cannot update another user's tournament
    public function testUserCannnotUpdateAnotherUsersTournament()
    {
        // Create Tournament with factory which also creates a new user.
        $tournament = \App\Tournament::factory()->create();

        // Create a new user and sign in
        $this->signIn();
        $attributes = $this->getTournamentAttributes();

        // Current user is Forbidden to update a Tournament which they dont own.
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)->assertForbidden();
    }

    // Data must be valid when updating tournament
    public function testDataMustBeValidWhenUpdatingATournament()
    {
        $user = $this->signIn();
        $tournament = \App\Tournament::factory()->create(['user_id' => $user->id]);

        // limit must exist
        $attributes = ['limit_id' => 999];
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)->assertStatus(422);

        // variant must exist
        $attributes = ['variant_id' => 999];
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)->assertStatus(422);

        // location must be non empty
        $attributes = ['location' => ''];
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)->assertStatus(422);

        // name must be a string
        $attributes = ['name' => 100];
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)->assertStatus(422);

        // prize_pool must be a number
        $attributes = ['prize_pool' => 'Not a number'];
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)->assertStatus(422);

        // prize_pool must be positive
        $attributes = ['prize_pool' => -100];
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)->assertStatus(422);

        // position must be a number
        $attributes = ['position' => 'Not a number'];
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)->assertStatus(422);

        // position must be positive
        $attributes = ['position' => -100];
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)->assertStatus(422);

        // entries must be a number
        $attributes = ['entries' => 'Not a number'];
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)->assertStatus(422);

        // entries must be positive
        $attributes = ['entries' => -100];
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)->assertStatus(422);

        // name can be empty
        $attributes = ['name' => ''];
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)->assertOk();

        // Empty data is valid
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), [])->assertOk();
    }

    // Start time cannot be in the future when updating
    public function testStartTimeCannotBeInTheFutureWhenUpdating()
    {
        $user = $this->signIn();
        $tournament = \App\Tournament::factory()->create(['user_id' => $user->id]);

        $attributes = ['start_time' => Carbon::create('+1 second')->toDateTimeString()];
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)->assertStatus(422);
    }

    // End time cannot be in the future when updating
    public function testEndTimeCannotBeInTheFutureWhenUpdating()
    {
        $user = $this->signIn();
        $tournament = \App\Tournament::factory()->create(['user_id' => $user->id]);

        $attributes = ['end_time' => Carbon::create('+1 second')->toDateTimeString()];
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)->assertStatus(422);
    }

    // Providing both start and end time, end time cannot be before start time
    public function testEndTimeCannotBeBeforeStartTimeWhenUpdating()
    {
        $user = $this->signIn();
        $tournament = \App\Tournament::factory()->create(['user_id' => $user->id]);

        // Try to end time one second before start time
        $time = Carbon::create('-1 hour');
        $attributes = [
            'start_time' => $time->toDateTimeString(),
            'end_time' => $time->copy()->subSeconds(1)->toDateTimeString(),
        ];
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)->assertStatus(422);
    }

    // Start time and end time can be the same when updating
    public function testStartTimeAndEndTimeCanTheSameWhenUpdating()
    {
        $user = $this->signIn();
        $tournament = \App\Tournament::factory()->create(['user_id' => $user->id]);

        // Updating where start time is same as end time
        $time = Carbon::create('-1 hour');
        $attributes = [
            'start_time' => $time->toDateTimeString(),
            'end_time' => $time->toDateTimeString(),
        ];

        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)->assertOk();
    }

    // Start time and end time can be exactly now when updating
    public function testStartTimeAndEndTimeCanBeExactlyNowWhenUpdating()
    {
        $user = $this->signIn();
        $tournament = \App\Tournament::factory()->create(['user_id' => $user->id]);

        // Update where start time is same as end time and both set to now
        $attributes = [
            'start_time' => Carbon::now()->toDateTimeString(),
            'end_time' => Carbon::now()->toDateTimeString(),
        ];

        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)->assertOk();
    }

    // Only providing end time, it cannot be before tournament's start time
    public function testEndTimeCannotBeBeforeStartTimeIfOnlyProvidingEndTimeInRequest()
    {
        $user = $this->signIn();

        // Create a Tournament which started 2 hours ago and ended 30 mins after.
        $start_time = Carbon::create('-2 hours');
        $tournament = \App\Tournament::factory()->create([
            'user_id' => $user->id,
            'start_time' => $start_time->toDateTimeString(),
            'end_time' => $start_time->copy()->addMinutes(30)->toDateTimeString(),
        ]);

        // Try to update only the end time where it is one second before it's saved start time.
        $attributes = [
            'end_time' => $start_time->copy()->subSeconds(1)
        ];

        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)->assertStatus(422);
    }

    // Only providing start time, it cannot be after tournament's end time
    public function testStartTimeCannotBeAfterEndTimeIfOnlyProvidingStartTimeInRequest()
    {
        $user = $this->signIn();

        // Create a Tournament which started 2 hours ago and ended 30 mins after.
        $start_time = Carbon::create('-2 hours');
        $tournament = \App\Tournament::factory()->create([
            'user_id' => $user->id,
            'start_time' => $start_time->toDateTimeString(),
            'end_time' => $start_time->copy()->addMinutes(30)->toDateTimeString(),
        ]);

        // Try to update only the start time where it is one second after it's saved end time.
        $attributes = [
            'start_time' => $start_time->copy()->addMinutes(30)->addSeconds(1)->subSeconds(1)
        ];

        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)->assertStatus(422);
    }

    // Cannot update tournament with new start time that clashes with another tournament
    public function testCannotUpdateCashGameWithTimesThatClashWithAnotherCashGame()
    {
        $user = $this->signIn();

        // Create a Tournament which started 10 hours ago and ended 30 mins after.
        $time = Carbon::create('-10 hours');
        $tournament1 = \App\Tournament::factory()->create([
            'user_id' => $user->id,
            'start_time' => $time->toDateTimeString(),
            'end_time' => $time->copy()->addMinutes(30)->toDateTimeString(),
        ]);

        // Create another Tournament to update, which started an hour ago and ended 30 mins after, so it does not clash with $tournament1
        $updateTime = Carbon::create('-2 hours');
        $tournamentToUpdate = \App\Tournament::factory()->create([
            'user_id' => $user->id,
            'start_time' => $updateTime->toDateTimeString(),
            'end_time' => $updateTime->copy()->addMinutes(30)->toDateTimeString(),
        ]);

        // NOTE: Still possible to have tournaments overlap as long as the update's start time is not between any tournament's running times.
        // Starting 1 second before another Tournament is valid, but then the end_time causes overlaps.
        // TODO: Do I reject complete overlap?  Need to modify user->tournamentsAtTime(start_time) to accept and check end time too

        // Use $time which is the time tournament1 was created

        // Start time one second after another Tournament's start time (so it's in between other start and end time)
        $attributes = ['start_time' => $time->copy()->addSeconds(1)->toDateTimeString()];
        $this->patchJson(route('tournament.update', ['tournament' => $tournamentToUpdate->id]), $attributes)->assertStatus(422);

        // Start time exactly the same as another Tournament's start time
        $attributes = ['start_time' => $tournament1->start_time->toDateTimeString()];
        $this->patchJson(route('tournament.update', ['tournament' => $tournamentToUpdate->id]), $attributes)->assertStatus(422);

        // Start time exactly the same as another Tournament's end_time
        $attributes = ['start_time' => $tournament1->end_time->toDateTimeString()];
        $this->patchJson(route('tournament.update', ['tournament' => $tournamentToUpdate->id]), $attributes)->assertStatus(422);
    }

    // Updating tournament's times does not clash with itself
    public function testUpdatingTournamentTimesDoesNotClashWithItself()
    {
        // Current Cash Game that's being updated is not included when checking for clashes

        $user = $this->signIn();

        // Create a Cash Game which which started 1 hour ago and ended 30 minutes after
        $time = Carbon::create('-1 hours');
        $tournament = \App\Tournament::factory()->create([
            'user_id' => $user->id,
            'start_time' => $time->toDateTimeString(),
            'end_time' => $time->copy()->addMinutes(30)->toDateTimeString(),
        ]);

        // Update start time to 45 minutes ago (so add 15 minutes to $time) which is in between it's current start and end times
        $attributes = ['start_time' => $time->copy()->addMinutes(15)->toDateTimeString()];
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)->assertOk();

        // Update start time it's current end time which is ok
        $attributes = ['start_time' => $tournament->end_time->toDateTimeString()];
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)->assertOk();
    }

    // User can delete their tournament
    public function testUserCanDeleteTheirTournament()
    {
        $user = $this->signIn();
        $tournament = \App\Tournament::factory()->create(['user_id' => $user->id]);

        $this->deleteJson(route('tournament.delete', ['tournament' => $tournament->id]))->assertOk();
        $this->assertEmpty($user->tournaments);
    }

    // Tournament must exist when deleting
    public function testTournamentMustExistWhenDeleting()
    {
        $this->signIn();

        $this->deleteJson(route('tournament.delete', ['tournament' => 1]))->assertNotFound();
    }

    // User cannot delete another user's tournament
    public function testUserCannotDeleteAnotherUsersTournament()
    {
        // Create Tournament with factory which also creates a new user.
        $tournament = \App\Tournament::factory()->create();

        // Create a new user and sign in
        $this->signIn();

        // Current user is Forbidden to delete a cash game they dont own
        $this->deleteJson(route('tournament.delete', ['tournament' => $tournament->id]))->assertForbidden();
    }
}
