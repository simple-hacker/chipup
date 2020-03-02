<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Transactions\CashOut;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CashOutTest extends TestCase
{
    use RefreshDatabase;

    public function testOnlyAuthenticatedUsersCanAddCashOut()
    {
        $user = factory('App\User')->create();
        $cash_game = $user->startCashGame();

        $this->postJson(route('cashout.add', ['cash_game' => $cash_game]), [
                    'amount' => 500
                ])
                ->assertUnauthorized();
    }

    public function testACashOutCanBeAddedToACashGame()
    {       
        $cash_game = $this->signIn()->startCashGame();

        $this->postJson(route('cashout.add', ['cash_game' => $cash_game]), [
                    'amount' => 500
                ])
                ->assertOk()
                ->assertJsonStructure(['success', 'transaction']);;

        $this->assertInstanceOf(CashOut::class, $cash_game->cashOutModel);
        $this->assertEquals(500, $cash_game->cashOutModel()->first()->amount);
    }

    public function testACashOutCannotBeAddedToACashGameThatDoesNotExist()
    {
        $this->signIn();

        // ID 500 does not exist, assert 404
        $this->postJson(route('cashout.add', ['cash_game' => 500]), [
                    'amount' => 500
                ])
                ->assertNotFound();

        $this->assertCount(0, CashOut::all());
    }

    public function testUserCannotAddMultipleCashOutsToCashGame()
    {
        $cash_game = $this->signIn()->startCashGame();

        // Cash Out should be Ok
        $this->postJson(route('cashout.add', ['cash_game' => $cash_game]), [
                    'amount' => 500
                ])->assertOk();

        // Assert 422 to CashOut a second time.
        $this->postJson(route('cashout.add', ['cash_game' => $cash_game]), [
                    'amount' => 1000
                ])->assertStatus(422);

        // There should only be one instance of CashOut, and the CashGame profit will only be the first CashOut
        $this->assertInstanceOf(CashOut::class, $cash_game->cashOutModel);
        $this->assertCount(1, CashOut::all());
        $this->assertEquals(500, $cash_game->fresh()->profit);
    }

    public function testViewingCashOutReturnsJsonOfCashOutTransaction()
    {
        $cash_game = $this->signIn()->startCashGame();

        $this->postJson(route('cashout.add', ['cash_game' => $cash_game]), [
            'amount' => 500
        ]);

        $cash_out = $cash_game->cashOutModel;
        
        $this->getJson(route('cashout.view', [
                    'cash_out' => $cash_out
                ]))
                ->assertOk()
                ->assertJsonStructure(['success', 'transaction']);
    }

    public function testAUserCanUpdateTheCashOut()
    {
        $cash_game = $this->signIn()->startCashGame();

        $this->postJson(route('cashout.add', ['cash_game' => $cash_game]), [
            'amount' => 500
        ]);

        $cash_out = $cash_game->cashOutModel;
        
        // Change amount from 500 to 1000
        $response = $this->patchJson(route('cashout.update', ['cash_out' => $cash_out]), [
                                'amount' => 1000
                            ])
                            ->assertOk()
                            ->assertJsonStructure(['success', 'transaction']);

        $this->assertEquals(1000, $cash_out->fresh()->amount);
        $this->assertEquals(1000, $response['transaction']['amount']);
    }

    public function testAUserCanDeleteTheCashOut()
    {
        $cash_game = $this->signIn()->startCashGame();

        $this->postJson(route('cashout.add', ['cash_game' => $cash_game]), [
            'amount' => 500
        ]);

        $cash_out = $cash_game->cashOutModel()->first();
        
        // Change amount from 500 to 1000
        $this->deleteJson(route('cashout.update', ['cash_out' => $cash_out]))
                ->assertOk()
                ->assertJsonStructure(['success'])
                ->assertJson([
                    'success' => true
                ]);

        $this->assertCount(0, CashOut::all());
        $this->assertEmpty($cash_game->fresh()->cashOutModel);
    }

    public function testCashOutAmountIsValidForAdd()
    {
        $cash_game = $this->signIn()->startCashGame();

        // Test not sending amount
        $this->postJson(route('cashout.add', ['cash_game' => $cash_game]), [

                ])
                ->assertStatus(422);

        // Test float numbers
        $this->postJson(route('cashout.add', ['cash_game' => $cash_game]), [
                    'amount' => 55.52
                ])
                ->assertStatus(422);
                
        // Test negative numbers
        $this->postJson(route('cashout.add', ['cash_game' => $cash_game]), [
                    'amount' => -10
                ])
                ->assertStatus(422);

        // Test string
        $this->postJson(route('cashout.add', ['cash_game' => $cash_game]), [
                    'amount' => 'Invalid'
                ])
                ->assertStatus(422);

        // Zero should be okay
        $this->postJson(route('cashout.add', ['cash_game' => $cash_game]), [
                'amount' => 0
            ])
            ->assertOk();
    }

    public function testCashOutAmountIsValidForUpdate()
    {
        $cash_game = $this->signIn()->startCashGame();

        $this->postJson(route('cashout.add', ['cash_game' => $cash_game]), [
            'amount' => 500
        ]);
        $cash_out = $cash_game->cashOutModel()->first();

        // Empty POST data is OK because it doesn't change anything.
        $this->patchJson(route('cashout.update', ['cash_out' => $cash_out]), [

                ])
                ->assertOk();

        // Test float numbers
        $this->patchJson(route('cashout.update', ['cash_out' => $cash_out]), [
                    'amount' => 55.52
                ])
                ->assertStatus(422);
                
        // Test negative numbers
        $this->patchJson(route('cashout.update', ['cash_out' => $cash_out]), [
                    'amount' => -10
                ])
                ->assertStatus(422);

        // Test string
        $this->patchJson(route('cashout.update', ['cash_out' => $cash_out]), [
                    'amount' => 'Invalid'
                ])
                ->assertStatus(422);

        // Zero should be okay
        $this->patchJson(route('cashout.update', ['cash_out' => $cash_out]), [
                    'amount' => 0
                ])
                ->assertOk();
    }

    public function testTheCashOutMustBelongToTheAuthenticatedUser()
    {
        // User1 creates a CashGame and adds a CashOut
        $user1 = $this->signIn();
        $cash_game = $user1->startCashGame();
        $this->postJson(route('cashout.add', ['cash_game' => $cash_game]), ['amount' => 500]);
        $cash_out = $cash_game->cashOutModel()->first();

        // Create and sign in the second user
        $user2 = $this->signIn();

        // User2 tries to Add CashOut to User1's CashGame
        $this->postJson(route('cashout.add', ['cash_game' => $cash_game]), ['amount' => 1000])
                ->assertForbidden();

        // User2 tries to view User1's CashOut
        $this->getJson(route('cashout.view', ['cash_out' => $cash_out]))
                ->assertForbidden();

        // User2 tries to update User1's CashOut
        $this->patchJson(route('cashout.update', ['cash_out' => $cash_out]), ['amount' => 1000])
                ->assertForbidden();

        // User2 tries to delete User1's CashOut
        $this->deleteJson(route('cashout.delete', ['cash_out' => $cash_out]))
                ->assertForbidden();
    }
}
