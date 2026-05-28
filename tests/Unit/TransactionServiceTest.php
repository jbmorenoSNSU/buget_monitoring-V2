<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Category;
use App\Models\Person;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(TransactionService::class);
    $this->person  = Person::factory()->create();
    $this->type    = AccountType::factory()->create();
    $this->account = Account::factory()
        ->for($this->person)
        ->for($this->type)
        ->create(['current_balance' => 0]);
    $this->category = Category::factory()->create(['type' => 'income']);
});

it('creates an income transaction and increments account balance', function () {
    $transaction = $this->service->create([
        'account_id'       => $this->account->id,
        'category_id'      => $this->category->id,
        'type'             => 'income',
        'amount'           => 500.00,
        'transaction_date' => now()->toDateString(),
        'description'      => 'Salary',
    ]);

    expect($transaction->id)->toBeInt()
        ->and($this->account->fresh()->current_balance)->toEqual('500.00');
});

it('creates an expense transaction and decrements account balance', function () {
    $this->account->update(['current_balance' => 1000]);
    $expense_cat = Category::factory()->create(['type' => 'expense']);

    $this->service->create([
        'account_id'       => $this->account->id,
        'category_id'      => $expense_cat->id,
        'type'             => 'expense',
        'amount'           => 300.00,
        'transaction_date' => now()->toDateString(),
        'description'      => 'Groceries',
    ]);

    expect($this->account->fresh()->current_balance)->toEqual('700.00');
});

it('calculates monthly income correctly', function () {
    Transaction::factory()->for($this->account)->for($this->category)->create([
        'type'             => 'income',
        'amount'           => 1000,
        'transaction_date' => now()->startOfMonth(),
    ]);

    $income = $this->service->get_monthly_income(now()->month, now()->year);

    expect($income)->toBe(1000.0);
});

it('deletes a transaction and reverses the balance effect', function () {
    $transaction = $this->service->create([
        'account_id'       => $this->account->id,
        'category_id'      => $this->category->id,
        'type'             => 'income',
        'amount'           => 200.00,
        'transaction_date' => now()->toDateString(),
        'description'      => 'Bonus',
    ]);

    $this->service->delete($transaction);

    expect($this->account->fresh()->current_balance)->toEqual('0.00')
        ->and(Transaction::find($transaction->id))->toBeNull();
});
