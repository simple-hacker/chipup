<?php

namespace App\Providers;

use App\BuyIn;
use App\CashOut;
use App\Expense;
use App\BankrollTransaction;
use App\Observers\BuyInObserver;
use App\Observers\CashOutObserver;
use App\Observers\ExpenseObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Observers\BankrollTransactionObserver;

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
        BankrollTransaction::observe(BankrollTransactionObserver::class);
        BuyIn::observe(BuyInObserver::class);
        Expense::observe(ExpenseObserver::class);
        CashOut::observe(CashOutObserver::class);
    }
}
