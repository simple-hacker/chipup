<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Transactions\BuyIn;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BuyInTest extends TestCase
{
    use RefreshDatabase;

    public function testOnlyAuthenticatedUsersCanAddBuyIn()
    {
        $user = factory('App\User')->create();
        $cash_game = $user->startCashGame();

        $this->postJson(route('buyin.add', ['cash_game' => $cash_game]), [
                    'amount' => 500
                ])
                ->assertUnauthorized();
    }

    public function testABuyInCanBeAddedToACashGame()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);
        $cash_game = $user->startCashGame();

        $this->postJson(route('buyin.add', ['cash_game' => $cash_game]), [
                    'amount' => 500
                ])
                ->assertOk()
                ->assertJsonStructure(['success', 'transaction']);;

        $this->assertCount(1, $cash_game->buyIns);
        $this->assertEquals(500, $cash_game->buyIns()->first()->amount);
    }

    public function testABuyInCannotBeAddedToACashGameThatDoesNotExist()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // ID 500 does not exist, assert 404
        $this->postJson(route('buyin.add', ['cash_game' => 500]), [
                    'amount' => 500
                ])
                ->assertNotFound();

        $this->assertCount(0, BuyIn::all());
    }

    public function testUserCanAddMultipleBuyInsToCashGame()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);
        $cash_game = $user->startCashGame();

        $this->postJson(route('buyin.add', ['cash_game' => $cash_game]), [
            'amount' => 500
        ]);
        $this->postJson(route('buyin.add', ['cash_game' => $cash_game]), [
            'amount' => 1000
        ]);

        $this->assertCount(2, $cash_game->buyIns);
        $this->assertEquals(-1500, $cash_game->fresh()->profit);
    }

    public function testViewingBuyInReturnsJsonOfBuyInTransaction()
    {
        $this->withoutExceptionHandling();

        $user = factory('App\User')->create();
        $this->actingAs($user);
        $cash_game = $user->startCashGame();

        $this->postJson(route('buyin.add', ['cash_game' => $cash_game]), [
            'amount' => 500
        ]);

        $buy_in = $cash_game->buyIns()->first();
        
        $response = $this->getJson(route('buyin.view', [
                                'buy_in' => $buy_in
                            ]))
                            ->assertOk()
                            ->assertJsonStructure(['success', 'transaction']);
    }

    public function testAUserCanUpdateTheBuyIn()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);
        $cash_game = $user->startCashGame();

        $this->postJson(route('buyin.add', ['cash_game' => $cash_game]), [
            'amount' => 500
        ]);

        $buy_in = $cash_game->buyIns()->first();
        
        // Change amount from 500 to 1000
        $response = $this->patchJson(route('buyin.update', ['buy_in' => $buy_in]), [
                                'amount' => 1000
                            ])
                            ->assertOk()
                            ->assertJsonStructure(['success', 'transaction']);

        $this->assertEquals(1000, $buy_in->fresh()->amount);
        $this->assertEquals(1000, $response['transaction']['amount']);
    }

    public function testAUserCanDeleteTheBuyIn()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);
        $cash_game = $user->startCashGame();

        $this->postJson(route('buyin.add', ['cash_game' => $cash_game]), [
            'amount' => 500
        ]);

        $buy_in = $cash_game->buyIns()->first();
        
        // Change amount from 500 to 1000
        $response = $this->deleteJson(route('buyin.update', ['buy_in' => $buy_in]))
                            ->assertOk()
                            ->assertJsonStructure(['success'])
                            ->assertJson([
                                'success' => true
                            ]);;

        $this->assertCount(0, $cash_game->fresh()->buyIns);
    }

    public function testBuyInAmountIsValidForAdd()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);
        $cash_game = $user->startCashGame();

        // Test not sending amount
        $this->postJson(route('buyin.add', ['cash_game' => $cash_game]), [

                ])
                ->assertStatus(422);

        // Test float numbers
        $this->postJson(route('buyin.add', ['cash_game' => $cash_game]), [
                    'amount' => 55.52
                ])
                ->assertStatus(422);
                
        // Test negative numbers
        $this->postJson(route('buyin.add', ['cash_game' => $cash_game]), [
                    'amount' => -10
                ])
                ->assertStatus(422);

        // Test string
        $this->postJson(route('buyin.add', ['cash_game' => $cash_game]), [
                    'amount' => 'Invalid'
                ])
                ->assertStatus(422);

        // Zero should be okay
        $this->postJson(route('buyin.add', ['cash_game' => $cash_game]), [
                'amount' => 0
            ])
            ->assertOk();
    }

    public function testBuyInAmountIsValidForUpdate()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);
        $cash_game = $user->startCashGame();

        $this->postJson(route('buyin.add', ['cash_game' => $cash_game]), [
            'amount' => 500
        ]);
        $buy_in = $cash_game->buyIns()->first();

        // Test not sending amount
        $this->patchJson(route('buyin.update', ['buy_in' => $buy_in]), [

                ])
                ->assertStatus(422);

        // Test float numbers
        $this->patchJson(route('buyin.update', ['buy_in' => $buy_in]), [
                    'amount' => 55.52
                ])
                ->assertStatus(422);
                
        // Test negative numbers
        $this->patchJson(route('buyin.update', ['buy_in' => $buy_in]), [
                    'amount' => -10
                ])
                ->assertStatus(422);

        // Test string
        $this->patchJson(route('buyin.update', ['buy_in' => $buy_in]), [
                    'amount' => 'Invalid'
                ])
                ->assertStatus(422);

        // Zero should be okay
        $this->patchJson(route('buyin.update', ['buy_in' => $buy_in]), [
                'amount' => 0
            ])
            ->assertOk();
    }

    public function testTheBuyInMustBelongToTheAuthenticatedUser()
    {
        // User1 creates a CashGame and adds a BuyIn
        $user1 = factory('App\User')->create();
        $this->actingAs($user1);
        $cash_game = $user1->startCashGame();
        $this->postJson(route('buyin.add', ['cash_game' => $cash_game]), ['amount' => 500]);
        $buy_in = $cash_game->buyIns()->first();

        // Create the second user
        $user2 = factory('App\User')->create();
        $this->actingAs($user2);

        // User2 tries to Add BuyIn to User1's CashGame
        $this->postJson(route('buyin.add', ['cash_game' => $cash_game]), ['amount' => 1000])
                ->assertForbidden();

        // User2 tries to view User1's BuyIn
        $this->getJson(route('buyin.view', ['buy_in' => $buy_in]))
                ->assertForbidden();

        // User2 tries to update User1's BuyIn
        $this->patchJson(route('buyin.update', ['buy_in' => $buy_in]), ['amount' => 1000])
                ->assertForbidden();

        // User2 tries to delete User1's BuyIn
        $this->deleteJson(route('buyin.delete', ['buy_in' => $buy_in]))
                ->assertForbidden();
    }
}
