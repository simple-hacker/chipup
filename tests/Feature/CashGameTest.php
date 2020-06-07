<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Transactions\CashOut;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CashGameTest extends TestCase
{
    use RefreshDatabase;

    /*
    * ==================================
    * INDEX
    * ==================================
    */

    // User must be logged in create/view/view all/update/delete cash game

    // User can create a cash game
    // Data must be valid when creating
    // Start time cannot be in the future when creating
    // End time cannot be in the future when creating
    // End time cannot be before start time
    // Start time and end time can be the same when creating
    // Start time and end time can be exactly now when creating
    // Cannot add cash game which clashes with another cash game
    // User can add a completed cash game with start time before a current live cash game
    // User cannot add a completed cash game if start_time clashes with a live cash game

    // At least one BuyIn must be supplied
    // User can add multiple BuyIns
    // BuyIn amounts CANNOT be zero
    // BuyIns must be valid.

    // Expenses can be supplied when creating a cash game
    // Expenses are optional
    // Expenses must be valid

    // Cash Out can be provided when creating a cash game
    // If CashOut not supplied then a CashOut is created with amount zero
    // Cash Out can be zero
    // Cash Out must be valid

    // User can view all their cash games
    // User can view their cash game
    // Cash game must exist when viewing
    // User cannot view another user's cash game

    // User can update cash game
    // Cash game must exist when updating
    // User cannot update another user's cash game
    // Data must be valid when updating cash game
    // Start time cannot be in the future when updating
    // End time cannot be in the future when updating.
    // Providing both start and end time, end time cannot be before start time
    // Start time and end time can be the same when updating
    // Start time and end time can be exactly now when updating
    // Only providing end time, it cannot be before cash game's start time
    // Only providing start time, it cannot be after cash game's end time
    // Cannot update cash game with new start time that clashes with another cash game
    // Updating cash game's times does not clash with itself

    // User can delete their cash game
    // Cash game must exist when deleting
    // User cannot delete another user's cash game

    /*
    * ==================================
    * TESTS
    * ==================================
    */

    // User must be logged in create/view/view all/update/delete cash game
    public function testUserMustBeLoggedInToCreateCashGame()
    {
        $cashGame = factory('App\CashGame')->create();
        $validAttributes = $this->getCashGameAttributes();

        $this->getJson(route('cash.index'))->assertUnauthorized();
        $this->postJson(route('cash.create'), $validAttributes)->assertUnauthorized();
        $this->getJson(route('cash.view', ['cash_game' => $cashGame->id]))->assertUnauthorized();
        $this->patchJson(route('cash.update', ['cash_game' => $cashGame->id]), $validAttributes)->assertUnauthorized();
        $this->deleteJson(route('cash.delete', ['cash_game' => $cashGame->id]))->assertUnauthorized();
    }

    // User can create a cash game
    public function testUserCanAddACompletedCashGame()
    {
        $user = $this->signIn();

        $attributes = $this->getCashGameAttributes();

        $this->postJson(route('cash.create'), $attributes)->assertOk();

        // Get the basic cash game attributes
        $cash_game_attributes = $attributes;
        unset($cash_game_attributes['buy_ins'], $cash_game_attributes['expenses'], $cash_game_attributes['cash_out']);

        $cash_game = $user->cashGames()->first();

        $this->assertCount(1, $user->cashGames);
        $this->assertDatabaseHas('cash_games', $cash_game_attributes);
        $this->assertCount(1, $cash_game->buyIns);
        $this->assertCount(2, $cash_game->expenses);
        $this->assertInstanceOf(CashOut::class, $cash_game->cashOut);
    }

    // Data must be valid when creating
    public function testRequiredDataMustBePresentAndValidWhenAddingCompletedCashGame()
    {
        $this->signIn();

        // Valid attributes.  Will change each one before testing.
        $validAttributes = $this->getCashGameAttributes();

        // start_time must be supplied
        $attributes = $validAttributes;
        unset($attributes['start_time']);
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // start_time be a date
        $attributes = $validAttributes;
        $attributes['start_time'] = 999; // Number
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
        $attributes = $validAttributes;
        $attributes['start_time'] = 'Not a date'; // Non date string
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // end_time must be supplied
        $attributes = $validAttributes;
        unset($attributes['end_time']);
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // end_time be a date
        $attributes = $validAttributes;
        $attributes['end_time'] = 999; // Number
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
        $attributes = $validAttributes;
        $attributes['end_time'] = 'Not a date'; // Non date string
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // stake must be supplied
        $attributes = $validAttributes;
        unset($attributes['stake_id']);
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // stake must exist in database
        $attributes = $validAttributes;
        $attributes['stake_id'] = 999;
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // variant must be supplied
        $attributes = $validAttributes;
        unset($attributes['variant_id']);
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // variant must exist in database
        $attributes = $validAttributes;
        $attributes['variant_id'] = 999;
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // limit must be supplied
        $attributes = $validAttributes;
        unset($attributes['limit_id']);
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // limit must exist in database
        $attributes = $validAttributes;
        $attributes['limit_id'] = 999;
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // table_size must be supplied
        $attributes = $validAttributes;
        unset($attributes['table_size_id']);
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // table_size must exist in database
        $attributes = $validAttributes;
        $attributes['table_size_id'] = 999;
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // location must be supplied
        $attributes = $validAttributes;
        unset($attributes['location']);
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // location must be a string
        $attributes = $validAttributes;
        $attributes['location'] = 999;
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
    }

    // Start time cannot be in the future when creating
    public function testStartTimeCannotBeInTheFutureWhenCreating()
    {
        $this->signIn();

        $attributes = $this->getCashGameAttributes();

        $attributes['start_time'] = Carbon::create('+1 seconds')->toDateTimeString();
        $attributes['end_time'] = Carbon::create('-10 minutes')->toDateTimeString();

        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
    }

    // End time cannot be in the future when creating
    public function testEndTimeCannotBeInTheFutureWhenCreating()
    {
        $this->signIn();

        $attributes = $this->getCashGameAttributes();

        $attributes['start_time'] = Carbon::create('-10 minutes')->toDateTimeString();
        $attributes['end_time'] = Carbon::create('+1 seconds')->toDateTimeString();

        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
    }

    // End time cannot be before start time
    public function testEndTimeCannotBeBeforeStartTimeWhenCreating()
    {
        $this->signIn();

        $attributes = $this->getCashGameAttributes();

        // Both times are in the past but end time is one second before start time
        $time = Carbon::create('-1 hour');
        $attributes['start_time'] = $time->toDateTimeString();
        $attributes['end_time'] = $time->copy()->subSeconds(1)->toDateTimeString();

        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
    }

    // Start time and end time can be the same when creating
    public function testStartTimeAndEndTimeCanTheSameWhenCreating()
    {
        $this->withoutExceptionHandling();
        
        $this->signIn();
        
        $attributes = $this->getCashGameAttributes();

        // Start time and end time are the same
        $attributes['start_time'] = Carbon::create('-10 minutes')->toDateTimeString();
        $attributes['end_time'] = Carbon::create('-10 minutes')->toDateTimeString();

        $this->postJson(route('cash.create'), $attributes)->assertOk();
    }

    // Start time and end time can be exactly now when creating
    public function testStartTimeAndEndTimeCanBeExactlyNowWhenCreating()
    {
        $this->signIn();
        
        $attributes = $this->getCashGameAttributes();

        // Start time and end time are the same and both set to now
        $attributes['start_time'] = Carbon::now()->toDateTimeString();
        $attributes['end_time'] = Carbon::now()->toDateTimeString();

        $this->postJson(route('cash.create'), $attributes)->assertOk();
    }

    // Cannot add cash game which clashes with another cash game
    public function testCannotCreateCashGameIfOneExistsBetweenStartAndEndTimes()
    {
        $user = $this->signIn();

        // Create a cash game with start time 1st May 2020 12:00 - 13:00
        $start_time = Carbon::create(2020, 05, 01, 12, 0, 0);
        $end_time = Carbon::create(2020, 05, 01, 13, 0, 0);

        factory('App\CashGame')->create([
            'user_id' => $user->id,
            'start_time' => $start_time->toDateTimeString(),
            'end_time' => $end_time->toDateTimeString(),
        ]);

        $attributes = $this->getCashGameAttributes();

        // Creating with start time equal to other start time
        $attributes['start_time'] = $start_time->copy()->toDateTimeString();
        $attributes['end_time'] = $end_time->copy()->addSeconds(1)->toDateTimeString();
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // Creating with start time in between to other start time and end time
        $attributes['start_time'] = $start_time->copy()->addSeconds(1)->toDateTimeString();
        $attributes['end_time'] = $end_time->copy()->addSeconds(1)->toDateTimeString();
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // Creating with start time in equal to other end time
        $attributes['start_time'] = $end_time->copy()->toDateTimeString();
        $attributes['end_time'] = $end_time->copy()->addSeconds(1)->toDateTimeString();
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // NOTE:
        // Creating a tournament with valid start time but clashing end_time is valid
        // End time is equal to other start time
        // TODO: Do I reject complete overlap?  Need to modify user->cashGamesAtTime(start_time) to accept and check end time too
        $attributes['start_time'] = $start_time->copy()->subSeconds(1)->toDateTimeString();
        $attributes['end_time'] = $start_time->copy()->toDateTimeString();
        $this->postJson(route('cash.create'), $attributes)->assertOk();
    }

    // User can add a completed cash game with start time before a current live cash game
    public function testUserCanAddCompletedCashGameBeforeALiveCashGame()
    {
        $this->signIn();

        // Start a Live CashGame 10 minutes ago.
        $time = Carbon::create('-10 minutes');
        $liveAttributes = $this->getLiveCashGameAttributes();
        $liveAttributes['start_time'] = $time->toDateTimeString();
        $this->post(route('cash.live.start'), $liveAttributes);

        // Create a completed Cash Game with start_time 1 hour before and end_time 30 minutes before.
        $validAttributes = $this->getCashGameAttributes();
        $attributes = $validAttributes;
        $attributes['start_time'] = $time->copy()->subMinutes(60)->toDateTimeString();
        $attributes['end_time'] = $time->copy()->subMinutes(30)->toDateTimeString();

        $this->postJson(route('cash.create'), $attributes)->assertOk();

        // NOTE: Currently clashes are still possible because if you create a completed tournament
        // with start time one second before live tournament but end time is during live session.
        // Start time is 10 minutes and 1 second ago
        // End time is 9 minutes ago.
        $attributes = $validAttributes;
        $attributes['start_time'] = $time->copy()->subSeconds(1)->toDateTimeString();
        $attributes['end_time'] = $time->copy()->addMinutes(1)->toDateTimeString();

        $this->postJson(route('cash.create'), $attributes)->assertOk();
    }

    // User cannot add a completed cash game if start_time clashes with a live cash game
    public function testUserCannotAddCompletedCashGameIfStartTimeClashesALiveCashGame()
    {
        $this->signIn();

        // Start a Live CashGame 10 minutes ago.
        $time = Carbon::create('-10 mins');
        $liveAttributes = $this->getLiveCashGameAttributes();
        $liveAttributes['start_time'] = $time->toDateTimeString();
        $this->post(route('cash.live.start'), $liveAttributes);

        // Create a completed Cash Game with start time 1 second after live cash game
        $attributes = $this->getCashGameAttributes();
        $attributes['start_time'] = $time->copy()->addSeconds(1)->toDateTimeString();
        $attributes['end_time'] = $time->copy()->addSeconds(2)->toDateTimeString();

        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
    }

    // At least one BuyIn must be supplied
    public function testBuyInsMustBeSuppliedWhenAddingACompletedCashGame()
    {
        $this->signIn();

        $attributes = $this->getCashGameAttributes();
        
        $attributes['buy_ins'] = [];
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        unset($attributes['buy_ins']);
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
    }

    // User can add multiple BuyIns
    public function testCashGamesCanHaveMultipleBuyIns()
    {
        $user = $this->signIn();

        $attributes = $this->getCashGameAttributes();
        $attributes['buy_ins'] = [
            ['amount' => 100],
            ['amount' => 50],
            ['amount' => 75],
        ];

        $this->postJson(route('cash.create'), $attributes)->assertOk();
        $this->assertCount(3, $user->cashGames->first()->buyIns);
    }

    // BuyIn amounts CANNOT be zero
    public function testBuyInAmountsCannotBeZero()
    {
        $this->signIn();

        $attributes = $this->getCashGameAttributes();
        $attributes['buy_ins'] = [
            ['amount' => 0],
        ];

        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
    }

    // BuyIns must be valid.
    public function testBuyInsMustBeValid()
    {
        $this->signIn();

        $attributes = $this->getCashGameAttributes();

        // BuyIn amounts must be an integer
        $attributes['buy_ins'] = [
            ['amount' => 'Not a number'],
        ];
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // BuyIn amounts cannot be negative
        $attributes['buy_ins'] = [
            ['amount' => -100],
        ];
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
    }

    // Expenses can be supplied when creating a cash game
    public function testExpensesCanBeSuppliedWhenCreatingCashGame()
    {
        $user = $this->signIn();

        // Add two expenses.
        $attributes = $this->getCashGameAttributes();
        $attributes['expenses'] = [
            ['amount' => 400],
            ['amount' => 750, 'comments' => 'Tips'],
        ];
        $this->postJson(route('cash.create'), $attributes)->assertOk();

        $this->assertCount(2, $user->cashGames->first()->expenses);
        $this->assertEquals(400, $user->cashGames->first()->expenses[0]->amount);
        $this->assertEquals(750, $user->cashGames->first()->expenses[1]->amount);
    }

    // Expenses are optional
    public function testExpensesAreOptionalWhenCreatingCashGame()
    {
        $user = $this->signIn();

        $attributes = $this->getCashGameAttributes();
        unset($attributes['expenses']);
        $this->postJson(route('cash.create'), $attributes)->assertOk();

        $this->assertCount(0, $user->cashGames->first()->expenses);
    }

    // Expenses must be valid
    public function testExpensesMustBeValidIfAdding()
    {
        $this->signIn();

        $attributes = $this->getCashGameAttributes();

        // Expense amount must be an integer
        $attributes['expenses'] = [
            ['amount' => 'Not a number']
        ];
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // Expense amount must be postive
        $attributes['expenses'] = [
            ['amount' => -100]
        ];
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // Expense amount cannot be zero
        $attributes['expenses'] = [
            ['amount' => 0]
        ];
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // Expense amount must be present if adding comments
        $attributes['expenses'] = [
            ['comments' => 'Expenses comment']
        ];
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);
    }

    // Cash Out can be provided when creating a cash game
    public function testCashOutCanBeSuppliedWhenCreatingCashGame()
    {
        $user = $this->signIn();

        $attributes = $this->getCashGameAttributes();
        $attributes['cash_out'] = ['amount' => 555];
        $this->postJson(route('cash.create'), $attributes)->assertOk();

        $this->assertEquals(555, $user->cashGames->first()->cashOut->amount);
    }

    // If CashOut not supplied then a CashOut is created with amount zero
    public function testNotSupplyingCashOutDefaultsToZero()
    {
        $user = $this->signIn();

        $attributes = $this->getCashGameAttributes();
        unset($attributes['cash_out']);
        $this->postJson(route('cash.create'), $attributes)->assertOk();

        // Assert CashOut Transaction was created and amount equal to 0
        $this->assertInstanceOf(CashOut::class, $user->cashGames->first()->cashOut);
        $this->assertEquals(0, $user->cashGames->first()->cashOut->amount);
    }

    // Cash Out can be zero
    public function testCashOutAmountCanBeZero()
    {
        $user = $this->signIn();

        $attributes = $this->getCashGameAttributes();
        $attributes['cash_out'] = ['amount' => 0];
        $this->postJson(route('cash.create'), $attributes)->assertOk();

        $this->assertEquals(0, $user->cashGames->first()->cashOut->amount);
    }

    // Cash Out must be valid
    public function testCashOutMustBeValidIfAdding()
    {
        $this->signIn();

        $attributes = $this->getCashGameAttributes();
        
        // CashOut amount must be a number
        $attributes['cash_out'] = ['amount' => 'Not a number'];
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // CashOut amount must be positive
        $attributes['cash_out'] = ['amount' => -100];
        $this->postJson(route('cash.create'), $attributes)->assertStatus(422);

        // CashOut can be an empty array as it default to zero
        $attributes['cash_out'] = [];
        $this->postJson(route('cash.create'), $attributes)->assertOk();
    }

    // User can view all their cash games
    public function testUserCanGetAllTheirCashGamesAsJson()
    {   
        $user = $this->signIn();

        // Assert response cash games is empty if no cash games exist
        $response = $this->getJson(route('cash.index'))->assertOk()->assertJsonStructure(['success', 'cash_games']);
        $this->assertEmpty($response['cash_games']);

        // Create three cash games
        factory('App\CashGame', 3)->create(['user_id' => $user->id]);
        
        // Assert response cash_games returns two cash games
        $response = $this->getJson(route('cash.index'))->assertOk()->assertJsonStructure(['success', 'cash_games']);
        $this->assertCount(3, $response['cash_games']);
    }

    // User can view their cash game
    public function testUserCanViewAValidCashGame()
    {
        $user = $this->signIn();
        $cash_game = factory('App\CashGame')->create(['user_id' => $user->id]);

        // Assert Not Found if supply incorrect cash_game id
        $this->getJson(route('cash.view', ['cash_game' => $cash_game->id]))
                ->assertOk()
                ->assertJsonStructure(['success', 'cash_game'])
                ->assertJson([
                    'success' => true,
                    'cash_game' => $cash_game->fresh()->toArray()
                ]);
    }

    // Cash game must exist when viewing
    public function testAssertNotFoundIfViewingInvalidCashGame()
    {
        $this->signIn();

        // Assert Not Found if supply incorrect cash_game id
        $this->getJson(route('cash.view', ['cash_game' => 99]))->assertNotFound();
    }

    // User cannot view another user's cash game
    public function testUserCannotViewAnotherUsersCashGame()
    {
        // Create a CashGame which also creates a user for it
        $cash_game = factory('App\CashGame')->create();

        // Create a new user and sign in
        $this->signIn();

        // Assert Forbidden if cash game does not belong to current user
        $this->getJson(route('cash.view', ['cash_game' => $cash_game->id]))->assertForbidden();
    }

    // User can update cash game
    public function testUserCanUpdateCashGameDetails()
    {
        $user = $this->signIn();
        $cash_game = factory('App\CashGame')->create(['user_id' => $user->id]);

        $attributes = [
            'start_time' => Carbon::create('-1 hour')->toDateTimeString(),
            'end_time' => Carbon::create('-30 minutes')->toDateTimeString(),
            'stake_id' => 1,
            'variant_id' => 1,
            'limit_id' => 1,
            'table_size_id' => 1,
            'location' => 'Updated Location',
            'comments' => 'Updated Comments',
        ];

        // Make sure new attributes are missing
        $this->assertDatabaseMissing('cash_games', $attributes);

        // Update and check database for updated attributes
        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), $attributes)->assertOk();

        $this->assertDatabaseHas('cash_games', $attributes);
    }

    // Cash game must exist when updating
    public function testCashGameMustExistWhenUpdating()
    {
        $this->signIn();

        $this->patchJson(route('cash.update', ['cash_game' => 1]))->assertNotFound();
    }

    // User cannot update another user's cash game
    public function testUserCannnotUpdateAnotherUsersCashGame()
    {
        // Create CashGame with factory which also creates a new user.
        $cash_game = factory('App\CashGame')->create();

        // Create a new user and sign in
        $this->signIn();
        $attributes = $this->getCashGameAttributes();

        // Current user is Forbidden to update a cash game which they dont own.
        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), $attributes)->assertForbidden();
    }

    // Data must be valid when updating cash game
    public function testDataMustBeValidWhenUpdatingACashGame()
    {
        $user = $this->signIn();
        $cash_game = factory('App\CashGame')->create(['user_id' => $user->id]);

        // stake must exist
        $attributes = ['stake_id' => 999];
        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), $attributes)->assertStatus(422);

        // limit must exist
        $attributes = ['limit_id' => 999];
        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), $attributes)->assertStatus(422);

        // variant must exist
        $attributes = ['variant_id' => 999];
        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), $attributes)->assertStatus(422);

        // table_size must exist
        $attributes = ['stake_id' => 999];
        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), $attributes)->assertStatus(422);

        // location must be non empty
        $attributes = ['location' => ''];
        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), $attributes)->assertStatus(422);

        // Empty data is valid
        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), [])->assertOk();
    }

    // Start time cannot be in the future when updating
    public function testStartTimeCannotBeInTheFutureWhenUpdating()
    {
        $user = $this->signIn();
        $cash_game = factory('App\CashGame')->create(['user_id' => $user->id]);

        $attributes = ['start_time' => Carbon::create('+1 second')->toDateTimeString()];
        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), $attributes)->assertStatus(422);
    }

    // End time cannot be in the future when updating.
    public function testEndTimeCannotBeInTheFutureWhenUpdating()
    {
        $user = $this->signIn();
        $cash_game = factory('App\CashGame')->create(['user_id' => $user->id]);

        $attributes = ['end_time' => Carbon::create('+1 second')->toDateTimeString()];
        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), $attributes)->assertStatus(422);
    }

    // Providing both start and end time, end time cannot be before start time
    public function testEndTimeCannotBeBeforeStartTimeWhenUpdating()
    {
        $user = $this->signIn();
        $cash_game = factory('App\CashGame')->create(['user_id' => $user->id]);

        // Try to end time one second before start time
        $time = Carbon::create('-1 hour');
        $attributes = [
            'start_time' => $time->toDateTimeString(),
            'end_time' => $time->copy()->subSeconds(1)->toDateTimeString(),
        ];
        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), $attributes)->assertStatus(422);
    }

    // Start time and end time can be the same when updating
    public function testStartTimeAndEndTimeCanTheSameWhenUpdating()
    {
        $user = $this->signIn();
        $cash_game = factory('App\CashGame')->create(['user_id' => $user->id]);

        // Updating where start time is same as end time
        $time = Carbon::create('-1 hour');
        $attributes = [
            'start_time' => $time->toDateTimeString(),
            'end_time' => $time->toDateTimeString(),
        ];

        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), $attributes)->assertOk();
    }

    // Start time and end time can be exactly now when updating
    public function testStartTimeAndEndTimeCanBeExactlyNowWhenUpdating()
    {
        $user = $this->signIn();
        $cash_game = factory('App\CashGame')->create(['user_id' => $user->id]);

        // Update where start time is same as end time and both set to now
        $attributes = [
            'start_time' => Carbon::now()->toDateTimeString(),
            'end_time' => Carbon::now()->toDateTimeString(),
        ];

        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), $attributes)->assertOk();
    }


    // Only providing end time, it cannot be before cash game's start time
    public function testEndTimeCannotBeBeforeStartTimeIfOnlyProvidingEndTimeInRequest()
    {
        $user = $this->signIn();

        // Create a Cash Game which started 2 hours ago and ended 30 mins after.
        $start_time = Carbon::create('-2 hours');
        $cash_game = factory('App\CashGame')->create([
            'user_id' => $user->id,
            'start_time' => $start_time->toDateTimeString(),
            'end_time' => $start_time->copy()->addMinutes(30)->toDateTimeString(),
        ]);

        // Try to update only the end time where it is one second before it's saved start time.
        $attributes = [
            'end_time' => $start_time->copy()->subSeconds(1)
        ];

        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), $attributes)->assertStatus(422);
    }

    // Only providing start time, it cannot be after cash game's end time
    public function testStartTimeCannotBeAfterEndTimeIfOnlyProvidingStartTimeInRequest()
    {
        $user = $this->signIn();

        // Create a Cash Game which started 2 hours ago and ended 30 mins after.
        $start_time = Carbon::create('-2 hours');
        $cash_game = factory('App\CashGame')->create([
            'user_id' => $user->id,
            'start_time' => $start_time->toDateTimeString(),
            'end_time' => $start_time->copy()->addMinutes(30)->toDateTimeString(),
        ]);

        // Try to update only the start time where it is one second after it's saved end time.
        $attributes = [
            'start_time' => $start_time->copy()->addMinutes(30)->addSeconds(1)->subSeconds(1)
        ];

        $this->patchJson(route('cash.update', ['cash_game' => $cash_game->id]), $attributes)->assertStatus(422);
    }

    // Cannot update cash game with new start time that clashes with another cash game
    public function testCannotUpdateCashGameWithTimesThatClashWithAnotherCashGame()
    {
        $user = $this->signIn();

        // Create a Cash Game which started 10 hours ago and ended 30 mins after.
        $time = Carbon::create('-10 hours');
        $cashGame1 = factory('App\CashGame')->create([
            'user_id' => $user->id,
            'start_time' => $time->toDateTimeString(),
            'end_time' => $time->copy()->addMinutes(30)->toDateTimeString(),
        ]);

        // Create another cash game to update, which started an hour ago and ended 30 mins after, so it does not clash with $cashGame1
        $updateTime = Carbon::create('-2 hours');
        $cashGameToUpdate = factory('App\CashGame')->create([
            'user_id' => $user->id,
            'start_time' => $updateTime->toDateTimeString(),
            'end_time' => $updateTime->copy()->addMinutes(30)->toDateTimeString(),
        ]);

        // NOTE: Still possible to have cash games overlap as long as the update's start time is not between any cash game's running times.
        // Starting 1 second before another cash game is valid, but then the end_time causes overlaps.
        // TODO: Do I reject complete overlap?  Need to modify user->cashGamesAtTime(start_time) to accept and check end time too

        // Use $time which is the time cashGame1 was created

        // Start time one second after another cash game's start time (so it's in between other start and end time)
        $attributes = ['start_time' => $time->copy()->addSeconds(1)->toDateTimeString()];
        $this->patchJson(route('cash.update', ['cash_game' => $cashGameToUpdate->id]), $attributes)->assertStatus(422);

        // Start time exactly the same as another cash game's start time
        $attributes = ['start_time' => $cashGame1->start_time->toDateTimeString()];
        $this->patchJson(route('cash.update', ['cash_game' => $cashGameToUpdate->id]), $attributes)->assertStatus(422);

        // Start time exactly the same as another cash game's end
        $attributes = ['start_time' => $cashGame1->end_time->toDateTimeString()];
        $this->patchJson(route('cash.update', ['cash_game' => $cashGameToUpdate->id]), $attributes)->assertStatus(422);
    }

    // Updating cash game's times does not clash with itself
    public function testUpdatingCashGameTimesDoesNotClashWithItself()
    {
        // Current Cash Game that's being updated is not included when checking for clashes

        $user = $this->signIn();

        // Create a Cash Game which which started 1 hour ago and ended 30 minutes after
        $time = Carbon::create('-1 hours');
        $cashGame = factory('App\CashGame')->create([
            'user_id' => $user->id,
            'start_time' => $time->toDateTimeString(),
            'end_time' => $time->copy()->addMinutes(30)->toDateTimeString(),
        ]);

        // Update start time to 45 minutes ago (so add 15 minutes to $time) which is in between it's current start and end times
        $attributes = ['start_time' => $time->copy()->addMinutes(15)->toDateTimeString()];
        $this->patchJson(route('cash.update', ['cash_game' => $cashGame->id]), $attributes)->assertOk();

        // Update start time it's current end time which is ok
        $attributes = ['start_time' => $cashGame->end_time->toDateTimeString()];
        $this->patchJson(route('cash.update', ['cash_game' => $cashGame->id]), $attributes)->assertOk();
    }

    // User can delete their cash game
    public function testUserCanDeleteTheirCashGame()
    {
        $user = $this->signIn();
        $cash_game = factory('App\CashGame')->create(['user_id' => $user->id]);

        $this->deleteJson(route('cash.delete', ['cash_game' => $cash_game->id]))->assertOk();
        $this->assertEmpty($user->cashGames);
    }

    // Cash game must exist when deleting
    public function testCashGameMustExistWhenDeleting()
    {
        $this->signIn();

        $this->deleteJson(route('cash.delete', ['cash_game' => 1]))->assertNotFound();
    }

    // User cannot delete another user's cash game
    public function testUserCannotDeleteAnotherUsersCashGame()
    {
        // Create CashGame with factory which also creates a new user.
        $cash_game = factory('App\CashGame')->create();

        // Create a new user and sign in
        $this->signIn();

        // Current user is Forbidden to delete a cash game they dont own
        $this->deleteJson(route('cash.delete', ['cash_game' => $cash_game->id]))->assertForbidden();
    }
}
