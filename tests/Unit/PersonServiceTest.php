<?php

declare(strict_types=1);

use App\Models\Person;
use App\Services\PersonService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(PersonService::class);
});

it('returns all active persons for dropdowns', function () {
    Person::factory()->create(['name' => 'Alice', 'is_active' => true]);
    Person::factory()->create(['name' => 'Bob',   'is_active' => false]);

    $active = $this->service->get_active();

    expect($active)->toHaveCount(1)
        ->and($active->first()->name)->toBe('Alice');
});

it('allows deletion of a person with no accounts', function () {
    $person = Person::factory()->create();

    expect($this->service->can_delete($person))->toBeTrue();
});

it('prevents deletion of a person who owns accounts', function () {
    $person = Person::factory()->hasAccounts(1)->create();

    expect($this->service->can_delete($person))->toBeFalse();
});

it('creates a person', function () {
    $person = $this->service->create(['name' => 'Charlie', 'color' => '#ff0000']);

    expect($person->name)->toBe('Charlie')
        ->and($person->color)->toBe('#ff0000');
});
