<?php

declare(strict_types=1);

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BudgetGoalController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RecurringTransactionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('accounts', AccountController::class)->except(['show']);
Route::patch('accounts/{account}/toggle', [AccountController::class, 'toggle'])->name('accounts.toggle');

Route::resource('categories', CategoryController::class)->except(['show']);
Route::patch('categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');

Route::resource('transactions', TransactionController::class)->except(['show']);

Route::resource('recurring', RecurringTransactionController::class)->except(['show']);
Route::patch('recurring/{recurring}/toggle', [RecurringTransactionController::class, 'toggle'])->name('recurring.toggle');
Route::post('recurring/generate-now', [RecurringTransactionController::class, 'generateNow'])->name('recurring.generate-now');

Route::resource('budget-goals', BudgetGoalController::class)->except(['show']);

Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/income-expense', [ReportController::class, 'incomeExpense'])->name('income-expense');
    Route::get('/category-expense', [ReportController::class, 'categoryExpense'])->name('category-expense');
    Route::get('/account-statement', [ReportController::class, 'accountStatement'])->name('account-statement');
    Route::get('/budget-goal', [ReportController::class, 'budgetGoal'])->name('budget-goal');
    Route::get('/export/{type}', [ReportController::class, 'export'])->name('export');
});
