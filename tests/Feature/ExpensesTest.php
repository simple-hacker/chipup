<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Transactions\Expense;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExpensesTest extends TestCase
{
    use RefreshDatabase;

    public function testOnlyAuthenticatedUsersCanAddExpense()
    {
        $user = factory('App\User')->create();
        $cash_game = $user->startCashGame();

        $this->postJson(route('expense.add', ['cash_game' => $cash_game]), [
                    'amount' => 500
                ])
                ->assertUnauthorized();
    }

    public function testAExpenseCanBeAddedToACashGame()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);
        $cash_game = $user->startCashGame();

        $this->postJson(route('expense.add', ['cash_game' => $cash_game]), [
                    'amount' => 500
                ])
                ->assertOk()
                ->assertJsonStructure(['success', 'transaction']);;

        $this->assertCount(1, $cash_game->expenses);
        $this->assertEquals(500, $cash_game->expenses()->first()->amount);
    }

    public function testAExpenseCannotBeAddedToACashGameThatDoesNotExist()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);

        // ID 500 does not exist, assert 404
        $this->postJson(route('expense.add', ['cash_game' => 500]), [
                    'amount' => 500
                ])
                ->assertNotFound();

        $this->assertCount(0, Expense::all());
    }

    public function testUserCanAddMultipleExpensesToCashGame()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);
        $cash_game = $user->startCashGame();

        $this->postJson(route('expense.add', ['cash_game' => $cash_game]), [
            'amount' => 500
        ]);
        $this->postJson(route('expense.add', ['cash_game' => $cash_game]), [
            'amount' => 1000
        ]);

        $this->assertCount(2, $cash_game->expenses);
        $this->assertEquals(-1500, $cash_game->fresh()->profit);
    }

    public function testViewingExpenseReturnsJsonOfExpenseTransaction()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);
        $cash_game = $user->startCashGame();

        $this->postJson(route('expense.add', ['cash_game' => $cash_game]), [
            'amount' => 500
        ]);

        $expense = $cash_game->expenses()->first();
        
        $response = $this->getJson(route('expense.view', [
                                'cash_game' => $cash_game,
                                'expense' => $expense
                            ]))
                            ->assertOk()
                            ->assertJsonStructure(['success', 'transaction']);
    }

    public function testAUserCanUpdateTheExpense()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);
        $cash_game = $user->startCashGame();

        $this->postJson(route('expense.add', ['cash_game' => $cash_game]), [
            'amount' => 500
        ]);

        $expense = $cash_game->expenses()->first();
        
        // Change amount from 500 to 1000
        $response = $this->patchJson(route('expense.update', ['cash_game' => $cash_game, 'expense' => $expense]), [
                                'amount' => 1000
                            ])
                            ->assertOk()
                            ->assertJsonStructure(['success', 'transaction']);

        $this->assertEquals(1000, $expense->fresh()->amount);
        $this->assertEquals(1000, $response['transaction']['amount']);
    }

    public function testAUserCanDeleteTheExpense()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);
        $cash_game = $user->startCashGame();

        $this->postJson(route('expense.add', ['cash_game' => $cash_game]), [
            'amount' => 500
        ]);

        $expense = $cash_game->expenses()->first();
        
        // Change amount from 500 to 1000
        $response = $this->deleteJson(route('expense.update', ['cash_game' => $cash_game, 'expense' => $expense]))
                            ->assertOk()
                            ->assertJsonStructure(['success'])
                            ->assertJson([
                                'success' => true
                            ]);;

        $this->assertCount(0, $cash_game->fresh()->expenses);
    }

    public function testBuyAmountIsValidForAdd()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);
        $cash_game = $user->startCashGame();

        // Test not sending amount
        $this->postJson(route('expense.add', ['cash_game' => $cash_game]), [

                ])
                ->assertStatus(422);

        // Test float numbers
        $this->postJson(route('expense.add', ['cash_game' => $cash_game]), [
                    'amount' => 55.52
                ])
                ->assertStatus(422);
                
        // Test negative numbers
        $this->postJson(route('expense.add', ['cash_game' => $cash_game]), [
                    'amount' => -10
                ])
                ->assertStatus(422);

        // Test string
        $this->postJson(route('expense.add', ['cash_game' => $cash_game]), [
                    'amount' => 'Invalid'
                ])
                ->assertStatus(422);

        // Zero should be okay
        $this->postJson(route('expense.add', ['cash_game' => $cash_game]), [
                'amount' => 0
            ])
            ->assertOk();
    }

    public function testBuyAmountIsValidForUpdate()
    {
        $user = factory('App\User')->create();
        $this->actingAs($user);
        $cash_game = $user->startCashGame();

        $this->postJson(route('expense.add', ['cash_game' => $cash_game]), [
            'amount' => 500
        ]);
        $expense = $cash_game->expenses()->first();

        // Test not sending amount
        $this->patchJson(route('expense.update', ['cash_game' => $cash_game, 'expense' => $expense]), [

                ])
                ->assertStatus(422);

        // Test float numbers
        $this->patchJson(route('expense.update', ['cash_game' => $cash_game, 'expense' => $expense]), [
                    'amount' => 55.52
                ])
                ->assertStatus(422);
                
        // Test negative numbers
        $this->patchJson(route('expense.update', ['cash_game' => $cash_game, 'expense' => $expense]), [
                    'amount' => -10
                ])
                ->assertStatus(422);

        // Test string
        $this->patchJson(route('expense.update', ['cash_game' => $cash_game, 'expense' => $expense]), [
                    'amount' => 'Invalid'
                ])
                ->assertStatus(422);

        // Zero should be okay
        $this->patchJson(route('expense.update', ['cash_game' => $cash_game, 'expense' => $expense]), [
                'amount' => 0
            ])
            ->assertOk();
    }

    public function testTheExpenseMustBelongToTheAuthenticatedUser()
    {
        // User1 creates a CashGame and adds a Expense
        $user1 = factory('App\User')->create();
        $this->actingAs($user1);
        $cash_game1 = $user1->startCashGame();
        $this->postJson(route('expense.add', ['cash_game' => $cash_game1]), ['amount' => 500]);
        $expense = $cash_game1->expenses()->first();

        // Create the second user
        $user2 = factory('App\User')->create();
        $this->actingAs($user2);

        // User2 tries to Add Expense to User1's CashGame
        $this->postJson(route('expense.add', ['cash_game' => $cash_game1]), ['amount' => 1000])
                ->assertForbidden();

        // User2 tries to view User1's Expense
        $this->getJson(route('expense.view', ['cash_game' => $cash_game1, 'expense' => $expense]))
                ->assertForbidden();

        // User2 tries to update User1's Expense
        $this->patchJson(route('expense.update', ['cash_game' => $cash_game1, 'expense' => $expense]), ['amount' => 1000])
                ->assertForbidden();

        // User2 tries to delete User1's Expense
        $this->deleteJson(route('expense.delete', ['cash_game' => $cash_game1, 'expense' => $expense]))
                ->assertForbidden();
    }
}
