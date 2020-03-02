<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Transactions\Expense;
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
        $cash_game = $this->signIn()->startCashGame();

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
        $this->signIn();

        // ID 500 does not exist, assert 404
        $this->postJson(route('expense.add', ['cash_game' => 500]), [
                    'amount' => 500
                ])
                ->assertNotFound();

        $this->assertCount(0, Expense::all());
    }

    public function testUserCanAddMultipleExpensesToCashGame()
    {
        $cash_game = $this->signIn()->startCashGame();

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
        $cash_game = $this->signIn()->startCashGame();

        $this->postJson(route('expense.add', ['cash_game' => $cash_game]), [
            'amount' => 500
        ]);

        $expense = $cash_game->expenses()->first();
        
        $this->getJson(route('expense.view', [
                    'cash_game' => $cash_game,
                    'expense' => $expense
                ]))
                ->assertOk()
                ->assertJsonStructure(['success', 'transaction']);
    }

    public function testAUserCanUpdateTheExpense()
    {
        $cash_game = $this->signIn()->startCashGame();

        $this->postJson(route('expense.add', ['cash_game' => $cash_game]), [
            'amount' => 500
        ]);

        $expense = $cash_game->expenses()->first();
        
        // Change amount from 500 to 1000
        $response = $this->patchJson(route('expense.update', ['expense' => $expense]), [
                                'amount' => 1000
                            ])
                            ->assertOk()
                            ->assertJsonStructure(['success', 'transaction']);

        $this->assertEquals(1000, $expense->fresh()->amount);
        $this->assertEquals(1000, $response['transaction']['amount']);
    }

    public function testAUserCanDeleteTheExpense()
    {
        $cash_game = $this->signIn()->startCashGame();

        $this->postJson(route('expense.add', ['cash_game' => $cash_game]), [
            'amount' => 500
        ]);

        $expense = $cash_game->expenses()->first();
        
        // Change amount from 500 to 1000
        $this->deleteJson(route('expense.update', ['expense' => $expense]))
                ->assertOk()
                ->assertJsonStructure(['success'])
                ->assertJson([
                    'success' => true
                ]);

        $this->assertCount(0, $cash_game->fresh()->expenses);
    }

    public function testExpenseAmountIsValidForAdd()
    {
        $cash_game = $this->signIn()->startCashGame();

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

    public function testExpenseAmountIsValidForUpdate()
    {
        $cash_game = $this->signIn()->startCashGame();

        $this->postJson(route('expense.add', ['cash_game' => $cash_game]), [
            'amount' => 500
        ]);
        $expense = $cash_game->expenses()->first();

        // Empty POST data is OK because it doesn't change anything.
        $this->patchJson(route('expense.update', ['expense' => $expense]), [

                ])
                ->assertOk();

        // Test float numbers
        $this->patchJson(route('expense.update', ['expense' => $expense]), [
                    'amount' => 55.52
                ])
                ->assertStatus(422);
                
        // Test negative numbers
        $this->patchJson(route('expense.update', ['expense' => $expense]), [
                    'amount' => -10
                ])
                ->assertStatus(422);

        // Test string
        $this->patchJson(route('expense.update', ['expense' => $expense]), [
                    'amount' => 'Invalid'
                ])
                ->assertStatus(422);

        // Zero should be okay
        $this->patchJson(route('expense.update', ['expense' => $expense]), [
                    'amount' => 0
                ])
                ->assertOk();
    }

    public function testTheExpenseMustBelongToTheAuthenticatedUser()
    {
        // User1 creates a CashGame and adds a Expense
        $user1 = $this->signIn();
        $cash_game1 = $user1->startCashGame();
        $this->postJson(route('expense.add', ['cash_game' => $cash_game1]), ['amount' => 500]);
        $expense = $cash_game1->expenses()->first();

        // Create and sign in the second user
        $user2 = $this->signIn();

        // User2 tries to Add Expense to User1's CashGame
        $this->postJson(route('expense.add', ['cash_game' => $cash_game1]), ['amount' => 1000])
                ->assertForbidden();

        // User2 tries to view User1's Expense
        $this->getJson(route('expense.view', ['expense' => $expense]))
                ->assertForbidden();

        // User2 tries to update User1's Expense
        $this->patchJson(route('expense.update', ['expense' => $expense]), ['amount' => 1000])
                ->assertForbidden();

        // User2 tries to delete User1's Expense
        $this->deleteJson(route('expense.delete', ['expense' => $expense]))
                ->assertForbidden();
    }

    public function testAnExpenseCanHaveComments()
    {
        $this->withoutExceptionHandling();

        $cash_game = $this->signIn()->startCashGame();

        // You can add a comment when adding an expense.
        $this->postJson(route('expense.add', ['cash_game' => $cash_game]), [
            'amount' => 500,
            'comments' => 'Comment'
        ]);

        $expense = $cash_game->expenses()->first();

        $this->assertEquals('Comment', $expense->comments);

        // You can also update the comments without sending amount
        $this->patchJson(route('expense.update', ['expense' => $expense]), [
            'comments' => 'Updated Comment'
        ]);

        $this->assertEquals('Updated Comment', $expense->fresh()->comments);
    }
}
