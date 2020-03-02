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
        $tournament = $user->startTournament();

        $this->postJson(route('rebuy.add', ['tournament' => $tournament]), [
                    'amount' => 500
                ])
                ->assertUnauthorized();
    }

    public function testARebuyCanBeAddedToATournament()
    {
        $tournament = $this->signIn()->startTournament();

        $this->postJson(route('rebuy.add', ['tournament' => $tournament]), [
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
        $this->postJson(route('rebuy.add', ['tournament' => 500]), [
                    'amount' => 500
                ])
                ->assertNotFound();

        $this->assertCount(0, Rebuy::all());
    }

    public function testUserCanAddMultipleRebuysToTournament()
    {
        $tournament = $this->signIn()->startTournament();

        $this->postJson(route('rebuy.add', ['tournament' => $tournament]), [
            'amount' => 500
        ]);
        $this->postJson(route('rebuy.add', ['tournament' => $tournament]), [
            'amount' => 1000
        ]);

        $this->assertCount(2, $tournament->rebuys);
        $this->assertEquals(-1500, $tournament->fresh()->profit);
    }

    public function testViewingRebuyReturnsJsonOfRebuyTransaction()
    {
        $tournament = $this->signIn()->startTournament();

        $this->postJson(route('rebuy.add', ['tournament' => $tournament]), [
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
        $tournament = $this->signIn()->startTournament();

        $this->postJson(route('rebuy.add', ['tournament' => $tournament]), [
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
        $tournament = $this->signIn()->startTournament();

        $this->postJson(route('rebuy.add', ['tournament' => $tournament]), [
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
        $tournament = $this->signIn()->startTournament();

        // Test not sending amount
        $this->postJson(route('rebuy.add', ['tournament' => $tournament]), [

                ])
                ->assertStatus(422);

        // Test float numbers
        $this->postJson(route('rebuy.add', ['tournament' => $tournament]), [
                    'amount' => 55.52
                ])
                ->assertStatus(422);
                
        // Test negative numbers
        $this->postJson(route('rebuy.add', ['tournament' => $tournament]), [
                    'amount' => -10
                ])
                ->assertStatus(422);

        // Test string
        $this->postJson(route('rebuy.add', ['tournament' => $tournament]), [
                    'amount' => 'Invalid'
                ])
                ->assertStatus(422);

        // Zero should be okay
        $this->postJson(route('rebuy.add', ['tournament' => $tournament]), [
                    'amount' => 0
                ])
                ->assertOk();
    }

    public function testRebuyAmountIsValidForUpdate()
    {
        $tournament = $this->signIn()->startTournament();

        $this->postJson(route('rebuy.add', ['tournament' => $tournament]), [
            'amount' => 500
        ]);
        $rebuy = $tournament->rebuys()->first();

        // Empty POST data is OK because it doesn't change anything.
        $this->patchJson(route('rebuy.update', ['rebuy' => $rebuy]), [

                ])
                ->assertOk();

        // Test float numbers
        $this->patchJson(route('rebuy.update', ['rebuy' => $rebuy]), [
                    'amount' => 55.52
                ])
                ->assertStatus(422);
                
        // Test negative numbers
        $this->patchJson(route('rebuy.update', ['rebuy' => $rebuy]), [
                    'amount' => -10
                ])
                ->assertStatus(422);

        // Test string
        $this->patchJson(route('rebuy.update', ['rebuy' => $rebuy]), [
                    'amount' => 'Invalid'
                ])
                ->assertStatus(422);

        // Zero should be okay
        $this->patchJson(route('rebuy.update', ['rebuy' => $rebuy]), [
                    'amount' => 0
                ])
                ->assertOk();
    }

    public function testTheRebuyMustBelongToTheAuthenticatedUser()
    {
        // User1 creates a Tournament and adds a Rebuy
        $user1 = $this->signIn();
        $tournament = $user1->startTournament();
        $this->postJson(route('rebuy.add', ['tournament' => $tournament]), ['amount' => 500]);
        $rebuy = $tournament->rebuys()->first();

        // Create and sign in the second user
        $user2 = $this->signIn();

        // User2 tries to Add Rebuy to User1's Tournament
        $this->postJson(route('rebuy.add', ['tournament' => $tournament]), ['amount' => 1000])
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
