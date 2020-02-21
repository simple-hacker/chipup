<?php

namespace App\Providers;

use App\Transactions\Bankroll;
use App\Transactions\BuyIn;
use App\Transactions\CashOut;
use App\Transactions\Expense;
use App\Observers\BankrollObserver;
use App\Observers\BuyInObserver;
use App\Observers\CashOutObserver;
use App\Observers\ExpenseObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Bankroll::observe(BankrollObserver::class);
        BuyIn::observe(BuyInObserver::class);
        Expense::observe(ExpenseObserver::class);
        CashOut::observe(CashOutObserver::class);
    }
}
