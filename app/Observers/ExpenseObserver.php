<?php

namespace App\Observers;

use App\Transactions\Expense;

class ExpenseObserver
{
    /**
     * Handle the expense "created" event.
     *
     * @param  \App\Transactions\Expense  $expense
     * @return void
     */
    public function created(Expense $expense)
    {
        $expense->game->decrement('profit', $expense->amount);
    }

    /**
     * Handle the expense "updated" event.
     *
     * @param  \App\Transactions\Expense  $expense
     * @return void
     */
    public function updated(Expense $expense)
    {
        // Find the difference needed for profit to be accurate.
        $amount = $expense->amount - $expense->getOriginal('amount');

        $expense->game->decrement('profit', $amount);
    }

    /**
     * Handle the expense "deleted" event.
     *
     * @param  \App\Transactions\Expense  $expense
     * @return void
     */
    public function deleted(Expense $expense)
    {
        // This is increment because a Expense is a decrement normally, so when it's 
        // deleted we add the amount back on.
        $expense->game->increment('profit', $expense->amount);
    }

    /**
     * Handle the expense "restored" event.
     *
     * @param  \App\Transactions\Expense  $expense
     * @return void
     */
    public function restored(Expense $expense)
    {
        //
    }

    /**
     * Handle the expense "force deleted" event.
     *
     * @param  \App\Transactions\Expense  $expense
     * @return void
     */
    public function forceDeleted(Expense $expense)
    {
        //
    }
}
