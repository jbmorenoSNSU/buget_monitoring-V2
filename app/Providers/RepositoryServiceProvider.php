<?php

declare(strict_types=1);

namespace App\Providers;

use App\Interfaces\AccountRepositoryInterface;
use App\Interfaces\BudgetGoalRepositoryInterface;
use App\Interfaces\CategoryRepositoryInterface;
use App\Interfaces\DebtRepositoryInterface;
use App\Interfaces\PersonRepositoryInterface;
use App\Interfaces\RecurringTransactionRepositoryInterface;
use App\Interfaces\SavingsGoalRepositoryInterface;
use App\Interfaces\TransactionRepositoryInterface;
use App\Repositories\EloquentAccountRepository;
use App\Repositories\EloquentBudgetGoalRepository;
use App\Repositories\EloquentCategoryRepository;
use App\Repositories\EloquentDebtRepository;
use App\Repositories\EloquentPersonRepository;
use App\Repositories\EloquentRecurringTransactionRepository;
use App\Repositories\EloquentSavingsGoalRepository;
use App\Repositories\EloquentTransactionRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register repository interface-to-implementation bindings.
     */
    public function register(): void
    {
        $this->app->bind(AccountRepositoryInterface::class, EloquentAccountRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, EloquentTransactionRepository::class);
        $this->app->bind(PersonRepositoryInterface::class, EloquentPersonRepository::class);
        $this->app->bind(BudgetGoalRepositoryInterface::class, EloquentBudgetGoalRepository::class);
        $this->app->bind(RecurringTransactionRepositoryInterface::class, EloquentRecurringTransactionRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(DebtRepositoryInterface::class, EloquentDebtRepository::class);
        $this->app->bind(SavingsGoalRepositoryInterface::class, EloquentSavingsGoalRepository::class);
    }

    public function boot(): void {}
}
