<?php

namespace App\Providers;

use App\Observers\GameObserver;
use App\Observers\BankrollTransactionObserver;
use App\Observers\GameTransactionObserver;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        \App\CashGame::observe(GameObserver::class);
        \App\Tournament::observe(GameObserver::class);
        \App\Transactions\Bankroll::observe(BankrollTransactionObserver::class);
        \App\Transactions\BuyIn::observe(GameTransactionObserver::class);
        \App\Transactions\Expense::observe(GameTransactionObserver::class);
        \App\Transactions\Rebuy::observe(GameTransactionObserver::class);
        \App\Transactions\AddOn::observe(GameTransactionObserver::class);
        \App\Transactions\CashOut::observe(GameTransactionObserver::class);
    }
}
