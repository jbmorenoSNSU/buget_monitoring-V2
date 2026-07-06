<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\BudgetGoal;
use App\Models\Category;
use App\Models\Debt;
use App\Models\RecurringTransaction;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Observers\InvalidateDashboardCacheObserver;
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

        // All financial models bust the dashboard + report caches on change.
        Transaction::observe(InvalidateDashboardCacheObserver::class);
        Account::observe(InvalidateDashboardCacheObserver::class);
        BudgetGoal::observe(InvalidateDashboardCacheObserver::class);
        Category::observe(InvalidateDashboardCacheObserver::class);
        Debt::observe(InvalidateDashboardCacheObserver::class);
        RecurringTransaction::observe(InvalidateDashboardCacheObserver::class);
        SavingsGoal::observe(InvalidateDashboardCacheObserver::class);
    }
}
