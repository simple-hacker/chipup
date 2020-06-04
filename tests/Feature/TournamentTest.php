<?php

namespace Tests\Feature;

use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
    // Data must be valid when creating
    // Start time cannot be in the future
    // End time cannot be in the future
    // End time cannot be before start time
    // Start time and end time can be the same
    // Start time and end time can be exactly now
    // Cannot add tournament which clashes with another tournament
    // User can add a completed tournament with start time before a current live tournament
    // User cannot add a completed tournament if start_time clashes with a live tournament

    // BuyIn must be supplied
    // Cannot add multiple BuyIns
    // BuyIn can be zero
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
    // End time cannot be in the future when updating.
    // Providing both start and end time, end time cannot be before start time
    // Start time and end time can be the same
    // Start time and end time can be exactly now
    // Only providing end time, it cannot be before tournament's start time
    // Only providing start time, it cannot be after tournament's end time
    // Cannot update tournament with new start time that clashes with another tournament

    // User can delete their tournament
    // Tournament must exist when deleting
    // User cannot delete another user's tournament

    /*
    * ==================================
    * TESTS
    * ==================================
    */










































    // public function testAUserCanGetAllTheirTournamentsAsJson()
    // {   
    //     $user = $this->signIn();

    //     // Assert response tournaments is empty if no tournaments exist
    //     $response = $this->getJson(route('tournament.index'))
    //             ->assertOk()
    //             ->assertJsonStructure(['success', 'tournaments']);

    //     $this->assertEmpty($response['tournaments']);

    //     // Create 2 Tournaments
    //     factory('App\Tournament', 2)->create(['user_id' => $user->id]);

    //     // Assert response tournaments returns two tournaments
    //     $response = $this->getJson(route('tournament.index'))
    //                         ->assertOk()
    //                         ->assertJsonStructure(['success', 'tournaments']);

    //     $this->assertCount(2, $response['tournaments']);
    // }

    // public function testAssertNotFoundIfViewingInvalidTournament()
    // {
    //     $this->signIn();

    //     // Assert Not Found if supply incorrect tournament id
    //     $this->getJson(route('tournament.view', ['tournament' => 99]))
    //             ->assertNotFound();
    // }

    // public function testAUserCanViewAValidTournament()
    // {
    //     $tournament = $this->startLiveTournament();

    //     $this->getJson(route('tournament.view', ['tournament' => $tournament->id]))
    //             ->assertOk()
    //             ->assertJsonStructure(['success', 'tournament']);
    // }

    // public function testAUserCannotViewAnotherUsersTournament()
    // {
    //     // Sign in User and create tournament
    //     $tournament = $this->startLiveTournament();

    //     //Sign in another user
    //     $this->signIn();

    //     // Assert Forbidden if tournament does not belong to user
    //     $this->getJson(route('tournament.view', ['tournament' => $tournament->id]))
    //             ->assertForbidden();
    // }

    // public function testAUserCanUpdateTheTournamentDetails()
    // {
    //     $tournament = $this->startLiveTournament();

    //     $start_time = Carbon::create('-1 hour')->toDateTimeString();

    //     $attributes = [
    //         'start_time' => $start_time,
    //         'limit_id' => 2,
    //         'variant_id' => 2,
    //         'location' => 'Las Vegas',
    //         'entries' => 300,
    //         'comments' => 'New comments',
    //     ];

    //     $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)
    //             ->assertOk()
    //             ->assertJsonStructure(['success', 'tournament'])
    //             ->assertJson([
    //                 'success' => true
    //             ]);

    //     $this->assertDatabaseHas('tournaments', $attributes);
    // }

    // public function testAUserCannnotUpdateAnotherUsersTournament()
    // {
    //     // Create a tournament belonging to new user.
    //     $tournament = factory('App\Tournament')->create();

    //     // Sign in as another new user
    //     $this->signIn();

    //     // Assert signed in user does not own the created tournament.
    //     $this->assertNotEquals($tournament->user_id, auth()->user()->id);

    //     $attributes = [
    //         'start_time' => '2020-02-02 18:00:00',
    //         'limit_id' => 2,
    //         'variant_id' => 2,
    //         'location' => 'Las Vegas',
    //         'entries' => 300,
    //         'comments' => 'New comments',
    //     ];

    //     $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), $attributes)
    //             ->assertForbidden();

    //     $this->assertDatabaseMissing('tournaments', $attributes);
    // }

    // public function testDataMustBeValidWhenUpdatingATournament()
    // {     
    //     $tournament = $this->signIn()->startTournament($this->getTournamentAttributes());
    //     $tournament->end();
        
    //     // Variant Id must exist in variants table
    //     $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), [
    //         'variant_id' => 999
    //     ])->assertStatus(422);

    //     // Start_time cannot be in the future
    //     $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), [
    //         'start_time' => Carbon::create('+1 second')->toDateTimeString()
    //     ])->assertStatus(422);

    //     // end_time cannot be in the future
    //     $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), [
    //         'end_time' => Carbon::create('+1 second')->toDateTimeString()
    //     ])->assertStatus(422);

    //     // Start_time cannot be after end_time
    //     $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), [
    //         'start_time' => Carbon::create('-10 minutes')->toDateTimeString(),
    //         'end_time' => Carbon::create('-20 minutes')->toDateTimeString(),
    //     ])->assertStatus(422);

    //     // Start_time and end_time cannot be the same
    //     $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), [
    //         'start_time' => Carbon::create('-10 minutes')->toDateTimeString(),
    //         'end_time' => Carbon::create('-10 minutes')->toDateTimeString(),
    //     ])->assertStatus(422);

    //     // Empty data is valid
    //     $this->patchJson(route('tournament.update', ['tournament' => $tournament->id]), [])->assertOk();
    // }

    // public function testAUserCanDeleteTheirTournament()
    // {
    //     $this->withoutExceptionHandling();

    //     $user = $this->signIn();
    //     $tournament = $user->startTournament($this->getTournamentAttributes());

    //     $this->deleteJson(route('tournament.delete', ['tournament' => $tournament->id]))->assertOk();

    //     $this->assertEmpty($user->tournaments);
    // }

    // public function testAUserCannotDeleteAnotherUsersTournament()
    // {
    //     // Sign in new user and create tournament
    //     $tournament = $this->startLiveTournament();

    //     //Sign in as another new user
    //     $this->signIn();

    //     // User 2 is Forbidden to delete user 1s tournament.
    //     $this->deleteJson(route('tournament.delete', ['tournament' => $tournament->id]))->assertForbidden();
    // }
}
