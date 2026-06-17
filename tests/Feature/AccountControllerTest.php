<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->person = Person::factory()->create();
    $this->type = AccountType::factory()->create();
});

it('renders the accounts index page with accounts and total balance', function () {
    Account::factory()->for($this->person)->for($this->type)
        ->create(['name' => 'Savings', 'current_balance' => 5000, 'is_active' => true]);

    $response = $this->get(route('accounts.index'));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Accounts/Index')
            ->has('accounts')
            ->has('totalBalance')
        );
});

it('stores a new account and redirects', function () {
    $response = $this->from(route('accounts.index'))->post(route('accounts.store'), [
        'account_type_id' => $this->type->id,
        'person_id' => $this->person->id,
        'name' => 'My New Account',
        'initial_balance' => 1000,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('accounts.index'));
    assertDatabaseHas('accounts', ['name' => 'My New Account']);
});

it('returns 422 on invalid store request', function () {
    $response = $this->postJson(route('accounts.store'), []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'account_type_id']);
});

it('updates an account', function () {
    $account = Account::factory()->for($this->person)->for($this->type)->create(['name' => 'Old Name']);

    $response = $this->from(route('accounts.index'))->put(route('accounts.update', $account), [
        'account_type_id' => $this->type->id,
        'person_id' => $this->person->id,
        'name' => 'Updated Name',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('accounts.index'));
    assertDatabaseHas('accounts', ['name' => 'Updated Name']);
});

it('prevents deleting an account that has transactions', function () {
    $account = Account::factory()->for($this->person)->for($this->type)->create();
    $account->transactions()->create([
        'type' => 'income',
        'amount' => 100,
        'transaction_date' => now(),
        'description' => 'Test',
    ]);

    $response = $this->from(route('accounts.index'))->delete(route('accounts.destroy', $account));

    $response->assertRedirect(route('accounts.index'));
    assertDatabaseHas('accounts', ['id' => $account->id]);
});
