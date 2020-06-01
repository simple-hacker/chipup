<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Transactions\Rebuy;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RebuyTest extends TestCase
{
    use RefreshDatabase;

    public function testOnlyAuthenticatedUsersCanAddRebuy()
    {
        $user = factory('App\User')->create();
        $tournament = $user->startTournament($this->getTournamentAttributes());

        $this->postJson(route('rebuy.create'), [
                    'game_id' => $tournament->id,
                    'game_type' => $tournament->game_type,
                    'amount' => 500
                ])
                ->assertUnauthorized();
    }

    public function testARebuyCanBeAddedToATournament()
    {
        $tournament = $this->startLiveTournament();

        $this->postJson(route('rebuy.create'), [
                    'game_id' => $tournament->id,
                    'game_type' => $tournament->game_type,
                    'amount' => 500
                ])
                ->assertOk()
                ->assertJsonStructure(['success', 'transaction']);;

        $this->assertCount(1, $tournament->rebuys);
        $this->assertEquals(500, $tournament->rebuys()->first()->amount);
    }

    public function testARebuyCannotBeAddedToATournamentThatDoesNotExist()
    {
        $user = $this->signIn();

        // ID 500 does not exist, assert 404
        $this->postJson(route('rebuy.create'), [
                    'game_id' => 99,
                    'game_type' => 'tournament',
                    'amount' => 500
                ])
                ->assertNotFound();

        $this->assertCount(0, Rebuy::all());
    }

    public function testUserCanAddMultipleRebuysToTournament()
    {
        $tournament = $this->startLiveTournament();

        $this->postJson(route('rebuy.create'), [
            'game_id' => $tournament->id,
            'game_type' => $tournament->game_type,
            'amount' => 500
        ]);
        $this->postJson(route('rebuy.create'), [
            'game_id' => $tournament->id,
            'game_type' => $tournament->game_type,
            'amount' => 1000
        ]);

        $this->assertCount(2, $tournament->rebuys);
        $this->assertEquals(-1500, $tournament->fresh()->profit);
    }

    public function testViewingRebuyReturnsJsonOfRebuyTransaction()
    {
        $tournament = $this->startLiveTournament();

        $this->postJson(route('rebuy.create'), [
            'game_id' => $tournament->id,
            'game_type' => $tournament->game_type,
            'amount' => 500
        ]);

        $rebuy = $tournament->rebuys()->first();
        
        $this->getJson(route('rebuy.view', [
                    'rebuy' => $rebuy
                ]))
                ->assertOk()
                ->assertJsonStructure(['success', 'transaction']);
    }

    public function testAUserCanUpdateTheRebuy()
    {
        $tournament = $this->startLiveTournament();

        $this->postJson(route('rebuy.create'), [
            'game_id' => $tournament->id,
            'game_type' => $tournament->game_type,
            'amount' => 500
        ]);

        $rebuy = $tournament->rebuys()->first();
        
        // Change amount from 500 to 1000
        $response = $this->patchJson(route('rebuy.update', ['rebuy' => $rebuy]), [
                                'amount' => 1000
                            ])
                            ->assertOk()
                            ->assertJsonStructure(['success', 'transaction']);

        $this->assertEquals(1000, $rebuy->fresh()->amount);
        $this->assertEquals(1000, $response['transaction']['amount']);
    }

    public function testAUserCanDeleteTheRebuy()
    {
        $tournament = $this->startLiveTournament();

        $this->postJson(route('rebuy.create'), [
            'game_id' => $tournament->id,
            'game_type' => $tournament->game_type,
            'amount' => 500
        ]);

        $rebuy = $tournament->rebuys()->first();
        
        // Change amount from 500 to 1000
        $this->deleteJson(route('rebuy.update', ['rebuy' => $rebuy]))
                ->assertOk()
                ->assertJsonStructure(['success'])
                ->assertJson([
                    'success' => true
                ]);

        $this->assertCount(0, $tournament->fresh()->rebuys);
    }

    public function testRebuyAmountIsValidForAdd()
    {
        $tournament = $this->startLiveTournament();

        // Test not sending amount
        $this->postJson(route('rebuy.create'), [
                    'game_id' => $tournament->id,
                    'game_type' => $tournament->game_type,
                ])
                ->assertStatus(422);

        // NOTE: 2020-04-29 Float numbers are now valid.
        // Test float numbers
        $this->postJson(route('rebuy.create'), [
                    'game_id' => $tournament->id,
                    'game_type' => $tournament->game_type,
                    'amount' => 55.52
                ])
                ->assertOk();
                
        // Test negative numbers
        $this->postJson(route('rebuy.create'), [
                    'game_id' => $tournament->id,
                    'game_type' => $tournament->game_type,
                    'amount' => -10
                ])
                ->assertStatus(422);

        // Test string
        $this->postJson(route('rebuy.create'), [
                    'game_id' => $tournament->id,
                    'game_type' => $tournament->game_type,
                    'amount' => 'Invalid'
                ])
                ->assertStatus(422);

        // Zero should be okay
        // NOTE: 2020-06-01 Zero is no invalid on front end, though valid backend.
        $this->postJson(route('rebuy.create'), [
                    'game_id' => $tournament->id,
                    'game_type' => $tournament->game_type,
                    'amount' => 0
                ])
                ->assertStatus(422);
    }

    public function testRebuyAmountIsValidForUpdate()
    {
        $tournament = $this->startLiveTournament();

        $this->postJson(route('rebuy.create'), [
            'game_id' => $tournament->id,
            'game_type' => $tournament->game_type,
            'amount' => 500
        ]);
        $rebuy = $tournament->rebuys()->first();

        // Empty POST data is OK because it doesn't change anything.
        $this->patchJson(route('rebuy.update', ['rebuy' => $rebuy]), [])->assertOk();

        // NOTE: 2020-04-29 Float numbers are now valid.
        // Test float numbers
        $this->patchJson(route('rebuy.update', ['rebuy' => $rebuy]), ['amount' => 55.52])->assertOk();
                
        // Test negative numbers
        $this->patchJson(route('rebuy.update', ['rebuy' => $rebuy]), ['amount' => -10])->assertStatus(422);

        // Test string
        $this->patchJson(route('rebuy.update', ['rebuy' => $rebuy]), ['amount' => 'Invalid'])->assertStatus(422);

        // Zero should be okay
        // NOTE: 2020-06-01 Zero is no invalid on front end, though valid backend.
        $this->patchJson(route('rebuy.update', ['rebuy' => $rebuy]), ['amount' => 0])->assertStatus(422);
    }

    public function testTheRebuyMustBelongToTheAuthenticatedUser()
    {
        // User1 creates a Tournament and adds a Rebuy
        $user1 = $this->signIn();
        $tournament = $user1->startTournament($this->getTournamentAttributes());
        $this->postJson(route('rebuy.create'), [
                    'game_id' => $tournament->id,
                    'game_type' => $tournament->game_type,
                    'amount' => 500
                ]);
        $rebuy = $tournament->rebuys()->first();

        // Create and sign in the second user
        $user2 = $this->signIn();

        // User2 tries to Add Rebuy to User1's Tournament
        $this->postJson(route('rebuy.create'), [
                    'game_id' => $tournament->id,
                    'game_type' => $tournament->game_type,
                    'amount' => 1000
                ])
                ->assertForbidden();

        // User2 tries to view User1's Rebuy
        $this->getJson(route('rebuy.view', ['rebuy' => $rebuy]))
                ->assertForbidden();

        // User2 tries to update User1's Rebuy
        $this->patchJson(route('rebuy.update', ['rebuy' => $rebuy]), ['amount' => 1000])
                ->assertForbidden();

        // User2 tries to delete User1's Rebuy
        $this->deleteJson(route('rebuy.delete', ['rebuy' => $rebuy]))
                ->assertForbidden();
    }
}
