<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Person;
use App\Models\SavingsGoal;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->account = Account::factory()->create(['current_balance' => 10000.00]);
    $this->person = Person::factory()->create(['name' => 'John Doe']);
});

it('renders the savings goals index page with goals, accounts, and persons', function () {
    SavingsGoal::factory()->for($this->account)->create([
        'name' => 'Dream Car', 
        'target_amount' => 50000,
        'person_id' => $this->person->id
    ]);

    $response = $this->get(route('savings-goals.index'));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('SavingsGoals/Index')
            ->has('goals')
            ->has('accounts')
            ->has('persons')
        );
});

it('stores a new savings goal with owner and redirects', function () {
    $response = $this->post(route('savings-goals.store'), [
        'name' => 'Emergency Fund',
        'target_amount' => 10000.00,
        'current_amount' => 1500.00,
        'account_id' => $this->account->id,
        'person_id' => $this->person->id,
        'target_date' => now()->addMonths(6)->toDateString(),
    ]);

    $response->assertRedirect();
    assertDatabaseHas('savings_goals', [
        'name' => 'Emergency Fund', 
        'target_amount' => 10000.00,
        'person_id' => $this->person->id
    ]);
});

it('returns 422 on invalid savings goal store request', function () {
    $response = $this->postJson(route('savings-goals.store'), []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'target_amount']);
});

it('updates an existing savings goal owner and details', function () {
    $goal = SavingsGoal::factory()->for($this->account)->create(['name' => 'Old Goal Name', 'current_amount' => 1000]);

    $response = $this->put(route('savings-goals.update', $goal), [
        'name' => 'Updated Goal Name',
        'target_amount' => 5000.00,
        'current_amount' => 2500.00,
        'account_id' => $this->account->id,
        'person_id' => $this->person->id,
        'target_date' => now()->addMonths(3)->toDateString(),
    ]);

    $response->assertRedirect();
    assertDatabaseHas('savings_goals', [
        'id' => $goal->id,
        'name' => 'Updated Goal Name',
        'current_amount' => 2500.00,
        'person_id' => $this->person->id,
    ]);
});

it('deletes a savings goal', function () {
    $goal = SavingsGoal::factory()->for($this->account)->create(['name' => 'Delete Me']);

    $response = $this->delete(route('savings-goals.destroy', $goal));

    $response->assertRedirect();
    assertDatabaseMissing('savings_goals', ['id' => $goal->id]);
});
