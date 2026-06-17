<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\BudgetGoal;
use App\Models\Debt;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Observers\AccountObserver;
use App\Observers\InvalidateDashboardCacheObserver;
use App\Observers\TransactionObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Transaction::observe(TransactionObserver::class);
        Account::observe(AccountObserver::class);

        Transaction::observe(InvalidateDashboardCacheObserver::class);
        BudgetGoal::observe(InvalidateDashboardCacheObserver::class);
        Debt::observe(InvalidateDashboardCacheObserver::class);
        RecurringTransaction::observe(InvalidateDashboardCacheObserver::class);
    }
}
