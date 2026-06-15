<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Transaction;
use App\Observers\AccountObserver;
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
        
        \App\Models\Transaction::observe(\App\Observers\InvalidateDashboardCacheObserver::class);
        \App\Models\BudgetGoal::observe(\App\Observers\InvalidateDashboardCacheObserver::class);
        \App\Models\Debt::observe(\App\Observers\InvalidateDashboardCacheObserver::class);
        \App\Models\RecurringTransaction::observe(\App\Observers\InvalidateDashboardCacheObserver::class);
    }
}
