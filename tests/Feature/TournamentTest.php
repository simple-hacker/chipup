<?php

namespace Tests\Feature;

use App\Tournament;
use App\Attributes\Limit;
use App\Attributes\Variant;
use App\Attributes\TableSize;
use App\Transactions\CashOut;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TournamentTest extends TestCase
{
    use RefreshDatabase;

    // User must be logged in create/view/update/delete tournament

    // User can create a tournament
    // Data must be valid when creating
    // BuyIn can be zero
    // BuyIn must be valid.
    // Start date cannot be in the future
    // End date cannot be before start date
    // Cannot add tournament which clases with another tournament

    // Expenses can be supplied when creating a tournament
    // Expenses must be valid
    // Rebuys can be supplied when creating a tournament
    // Rebuys must be valid
    // Rebuys can be supplied when creating a tournament
    // Rebuys must be valid

    // Cash Out can be provided when creating a tournament
    // Cash Out must be valid
    // If CashOut not supplied then a CashOut is created with amount

    // User can update Tournament
    // User cannot update another user's tournament
    // Data must be valid when updating tournament
    // Start date cannot be in the future
    // End date cannot be before start date
    // Cannot update tournament with new times which clases with another tournament

    // User can view their tournament
    // User can view all their tournaments
    // User cannot view another user's tournaments

    // User can delete their tournament
    // User cannot delete another user's tournament













    

    public function testAUserCanGetAllTheirTournamentsAsJson()
    {   
        $user = $this->signIn();

        // Assert response tournaments is empty if no tournaments exist
        $response = $this->getJson(route('tournament.index'))
                ->assertOk()
                ->assertJsonStructure(['success', 'tournaments']);

        $this->assertEmpty($response['tournaments']);

        // Create 2 Tournaments
        factory('App\Tournament', 2)->create(['user_id' => $user->id]);

        // Assert response tournaments returns two tournaments
        $response = $this->getJson(route('tournament.index'))
                            ->assertOk()
                            ->assertJsonStructure(['success', 'tournaments']);

        $this->assertCount(2, $response['tournaments']);
    }

    public function testAssertNotFoundIfViewingInvalidTournament()
    {
        $this->signIn();

        // Assert Not Found if supply incorrect tournament id
        $this->getJson(route('tournament.view', ['tournament' => 99]))
                ->assertNotFound();
    }

    public function testAUserCanViewAValidTournament()
    {
        $tournament = $this->startLiveTournament();

        $this->getJson(route('tournament.view', ['tournament' => $tournament->id]))
                ->assertOk()
                ->assertJsonStructure(['success', 'tournament']);
    }

    public function testAUserCannotViewAnotherUsersTournament()
    {
        // Sign in User and create tournament
        $tournament = $this->startLiveTournament();

        //Sign in another user
        $this->signIn();

        // Assert Forbidden if tournament does not belong to user
        $this->getJson(route('tournament.view', ['tournament' => $tournament->id]))
                ->assertForbidden();
    }

    public function testAUserCanUpdateTheTournamentDetails()
    {
        $tournament = $this->startLiveTournament();

        $start_time = Carbon::create('-1 hour')->toDateTimeString();

        $attributes = [
            'start_time' => $start_time,
            'limit_id' => 2,
            'variant_id' => 2,
            'location' => 'Las Vegas',
            'entries' => 300,
            'comments' => 'New comments',
        ];

        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)
                ->assertOk()
                ->assertJsonStructure(['success', 'tournament'])
                ->assertJson([
                    'success' => true
                ]);

        $this->assertDatabaseHas('tournaments', $attributes);
    }

    public function testAUserCannnotUpdateAnotherUsersTournament()
    {
        // Create a tournament belonging to new user.
        $tournament = factory('App\Tournament')->create();

        // Sign in as another new user
        $this->signIn();

        // Assert signed in user does not own the created tournament.
        $this->assertNotEquals($tournament->user_id, auth()->user()->id);

        $attributes = [
            'start_time' => '2020-02-02 18:00:00',
            'limit_id' => 2,
            'variant_id' => 2,
            'location' => 'Las Vegas',
            'entries' => 300,
            'comments' => 'New comments',
        ];

        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)
                ->assertForbidden();

        $this->assertDatabaseMissing('tournaments', $attributes);
    }

    public function testDataMustBeValidWhenUpdatingATournament()
    {     
        $tournament = $this->signIn()->startTournament($this->getTournamentAttributes());
        $tournament->end();

        // Empty data is valid
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), [])->assertOk();

        // Variant Id must exist in variants table
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), [
            'variant_id' => 999
        ])->assertStatus(422);

        // Start_time cannot be in the future
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), [
            'start_time' => Carbon::create('+1 second')->toDateTimeString()
        ])->assertStatus(422);

        // end_time cannot be in the future
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), [
            'end_time' => Carbon::create('+1 second')->toDateTimeString()
        ])->assertStatus(422);

        // Start_time cannot be after end_time
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), [
            'start_time' => Carbon::create('-10 minutes')->toDateTimeString(),
            'end_time' => Carbon::create('-20 minutes')->toDateTimeString(),
        ])->assertStatus(422);

        // Start_time and end_time cannot be the same
        $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), [
            'start_time' => Carbon::create('-10 minutes')->toDateTimeString(),
            'end_time' => Carbon::create('-10 minutes')->toDateTimeString(),
        ])->assertStatus(422);
    }

    public function testAUserCanDeleteTheirTournament()
    {
        $this->withoutExceptionHandling();

        $user = $this->signIn();
        $tournament = $user->startTournament($this->getTournamentAttributes());

        $this->deleteJson(route('tournament.delete', ['tournament' => $tournament->id]))->assertOk();

        $this->assertEmpty($user->tournaments);
    }

    public function testAUserCannotDeleteAnotherUsersTournament()
    {
        // Sign in new user and create tournament
        $tournament = $this->startLiveTournament();

        //Sign in as another new user
        $this->signIn();

        // User 2 is Forbidden to delete user 1s tournament.
        $this->deleteJson(route('tournament.delete', ['tournament' => $tournament->id]))->assertForbidden();
    }
}
