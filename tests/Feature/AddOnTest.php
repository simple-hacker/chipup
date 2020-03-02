<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Transactions\AddOn;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AddOnTest extends TestCase
{
    use RefreshDatabase;

    public function testOnlyAuthenticatedUsersCanAddAddOn()
    {
        $user = factory('App\User')->create();
        $tournament = $user->startTournament();

        $this->postJson(route('addon.add', ['tournament' => $tournament]), [
                    'amount' => 500
                ])
                ->assertUnauthorized();
    }

    public function testAnAddOnCanBeAddedToATournament()
    {
        $tournament = $this->signIn()->startTournament();

        $this->postJson(route('addon.add', ['tournament' => $tournament]), [
                    'amount' => 500
                ])
                ->assertOk()
                ->assertJsonStructure(['success', 'transaction']);;

        $this->assertCount(1, $tournament->addOns);
        $this->assertEquals(500, $tournament->addOns()->first()->amount);
    }

    public function testAnAddOnCannotBeAddedToATournamentThatDoesNotExist()
    {
        $this->signIn();

        // ID 500 does not exist, assert 404
        $this->postJson(route('addon.add', ['tournament' => 500]), [
                    'amount' => 500
                ])
                ->assertNotFound();

        $this->assertCount(0, AddOn::all());
    }

    public function testUserCanAddMultipleAddOnsToTournament()
    {
        $tournament = $this->signIn()->startTournament();

        $this->postJson(route('addon.add', ['tournament' => $tournament]), [
            'amount' => 500
        ]);
        $this->postJson(route('addon.add', ['tournament' => $tournament]), [
            'amount' => 1000
        ]);

        $this->assertCount(2, $tournament->addOns);
        $this->assertEquals(-1500, $tournament->fresh()->profit);
    }

    public function testViewingAddOnReturnsJsonOfAddOnTransaction()
    {
        $tournament = $this->signIn()->startTournament();

        $this->postJson(route('addon.add', ['tournament' => $tournament]), [
            'amount' => 500
        ]);

        $add_on = $tournament->addOns()->first();
        
        $response = $this->getJson(route('addon.view', [
                                'add_on' => $add_on
                            ]))
                            ->assertOk()
                            ->assertJsonStructure(['success', 'transaction']);
    }

    public function testAUserCanUpdateTheAddOn()
    {
        $tournament = $this->signIn()->startTournament();

        $this->postJson(route('addon.add', ['tournament' => $tournament]), [
            'amount' => 500
        ]);

        $add_on = $tournament->addOns()->first();
        
        // Change amount from 500 to 1000
        $response = $this->patchJson(route('addon.update', ['add_on' => $add_on]), [
                                'amount' => 1000
                            ])
                            ->assertOk()
                            ->assertJsonStructure(['success', 'transaction']);

        $this->assertEquals(1000, $add_on->fresh()->amount);
        $this->assertEquals(1000, $response['transaction']['amount']);
    }

    public function testAUserCanDeleteTheAddOn()
    {
        $tournament = $this->signIn()->startTournament();

        $this->postJson(route('addon.add', ['tournament' => $tournament]), [
            'amount' => 500
        ]);

        $add_on = $tournament->addOns()->first();
        
        // Change amount from 500 to 1000
        $this->deleteJson(route('addon.update', ['add_on' => $add_on]))
                            ->assertOk()
                            ->assertJsonStructure(['success'])
                            ->assertJson([
                                'success' => true
                            ]);;

        $this->assertCount(0, $tournament->fresh()->addOns);
    }

    public function testAddOnAmountIsValidForAdd()
    {
        $tournament = $this->signIn()->startTournament();

        // Test not sending amount
        $this->postJson(route('addon.add', ['tournament' => $tournament]), [

                ])
                ->assertStatus(422);

        // Test float numbers
        $this->postJson(route('addon.add', ['tournament' => $tournament]), [
                    'amount' => 55.52
                ])
                ->assertStatus(422);
                
        // Test negative numbers
        $this->postJson(route('addon.add', ['tournament' => $tournament]), [
                    'amount' => -10
                ])
                ->assertStatus(422);

        // Test string
        $this->postJson(route('addon.add', ['tournament' => $tournament]), [
                    'amount' => 'Invalid'
                ])
                ->assertStatus(422);

        // Zero should be okay
        $this->postJson(route('addon.add', ['tournament' => $tournament]), [
                    'amount' => 0
                ])
                ->assertOk();
    }

    public function testAddOnAmountIsValidForUpdate()
    {
        $tournament = $this->signIn()->startTournament();

        $this->postJson(route('addon.add', ['tournament' => $tournament]), [
            'amount' => 500
        ]);
        $add_on = $tournament->addOns()->first();

        // Test not sending amount
        $this->patchJson(route('addon.update', ['add_on' => $add_on]), [

                ])
                ->assertStatus(422);

        // Test float numbers
        $this->patchJson(route('addon.update', ['add_on' => $add_on]), [
                    'amount' => 55.52
                ])
                ->assertStatus(422);
                
        // Test negative numbers
        $this->patchJson(route('addon.update', ['add_on' => $add_on]), [
                    'amount' => -10
                ])
                ->assertStatus(422);

        // Test string
        $this->patchJson(route('addon.update', ['add_on' => $add_on]), [
                    'amount' => 'Invalid'
                ])
                ->assertStatus(422);

        // Zero should be okay
        $this->patchJson(route('addon.update', ['add_on' => $add_on]), [
                    'amount' => 0
                ])
                ->assertOk();
    }

    public function testTheAddOnMustBelongToTheAuthenticatedUser()
    {
        // User1 creates a Tournament and adds a AddOn
        $user1 = $this->signIn();
        $tournament = $user1->startTournament();
        $this->postJson(route('addon.add', ['tournament' => $tournament]), ['amount' => 500]);
        $add_on = $tournament->addOns()->first();

        // Create and sign in the second user
        $user2 = $this->signIn();

        // User2 tries to Add AddOn to User1's Tournament
        $this->postJson(route('addon.add', ['tournament' => $tournament]), ['amount' => 1000])
                ->assertForbidden();

        // User2 tries to view User1's AddOn
        $this->getJson(route('addon.view', ['add_on' => $add_on]))
                ->assertForbidden();

        // User2 tries to update User1's AddOn
        $this->patchJson(route('addon.update', ['add_on' => $add_on]), ['amount' => 1000])
                ->assertForbidden();

        // User2 tries to delete User1's AddOn
        $this->deleteJson(route('addon.delete', ['add_on' => $add_on]))
                ->assertForbidden();
    }
}
