<?php

declare(strict_types=1);

use App\Actions\Account\CreateAccountAction;
use App\DTOs\AccountDTO;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Person;
use App\Services\AccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(AccountService::class);
});

it('returns all accounts ordered by name', function () {
    $person = Person::factory()->create();
    $type = AccountType::factory()->create();

    Account::factory()->for($person)->for($type)->create(['name' => 'Zebra Bank']);
    Account::factory()->for($person)->for($type)->create(['name' => 'Alpha Bank']);

    $accounts = $this->service->get_all();

    expect($accounts->first()->name)->toBe('Alpha Bank')
        ->and($accounts->last()->name)->toBe('Zebra Bank');
});

it('returns only active accounts', function () {
    $person = Person::factory()->create();
    $type = AccountType::factory()->create();

    Account::factory()->for($person)->for($type)->create(['is_active' => true, 'name' => 'Active']);
    Account::factory()->for($person)->for($type)->create(['is_active' => false, 'name' => 'Inactive']);

    $accounts = $this->service->get_active();

    expect($accounts)->toHaveCount(1)
        ->and($accounts->first()->name)->toBe('Active');
});

it('calculates total balance correctly', function () {
    $person = Person::factory()->create();
    $type = AccountType::factory()->create();

    Account::factory()->for($person)->for($type)->create(['is_active' => true, 'current_balance' => 1000]);
    Account::factory()->for($person)->for($type)->create(['is_active' => true, 'current_balance' => 500]);
    Account::factory()->for($person)->for($type)->create(['is_active' => false, 'current_balance' => 9999]);

    expect($this->service->get_total_balance())->toBe(1500.0);
});

it('creates an account with initial balance set as current balance', function () {
    $person = Person::factory()->create();
    $type = AccountType::factory()->create();

    $action = app(CreateAccountAction::class);
    $account = $action->execute(AccountDTO::fromArray([
        'person_id' => $person->id,
        'account_type_id' => $type->id,
        'name' => 'Test Account',
        'initial_balance' => 2500.00,
        'is_active' => true,
    ]));

    expect($account->current_balance)->toEqual('2500.00')
        ->and($account->name)->toBe('Test Account');
});

it('prevents deletion of an account that has transactions', function () {
    $person = Person::factory()->create();
    $type = AccountType::factory()->create();
    $account = Account::factory()->for($person)->for($type)->create();

    // Simulate a linked transaction count
    $account->transactions()->create([
        'type' => 'income',
        'amount' => 100,
        'transaction_date' => now(),
        'description' => 'Test',
    ]);

    expect($this->service->can_delete($account))->toBeFalse();
});
