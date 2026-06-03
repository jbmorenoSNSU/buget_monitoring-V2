<?php

declare(strict_types=1);

use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertSoftDeleted;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->person = Person::factory()->create();
});

it('renders the persons index page', function () {
    $response = $this->get(route('persons.index'));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('Persons/Index')->has('persons'));
});

it('stores a new person', function () {
    $response = $this->post(route('persons.store'), [
        'name' => 'Juan',
        'color' => '#3b82f6',
    ]);

    $response->assertRedirect(route('persons.index'));
    assertDatabaseHas('persons', ['name' => 'Juan']);
});

it('returns 422 when name is missing', function () {
    $response = $this->postJson(route('persons.store'), ['color' => '#000']);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('updates a person', function () {
    $response = $this->put(route('persons.update', $this->person), [
        'name' => 'Updated Name',
        'color' => '#ff0000',
    ]);

    $response->assertRedirect(route('persons.index'));
    assertDatabaseHas('persons', ['name' => 'Updated Name']);
});

it('deletes a person with no accounts', function () {
    $response = $this->delete(route('persons.destroy', $this->person));

    $response->assertRedirect(route('persons.index'));
    assertSoftDeleted('persons', ['id' => $this->person->id]);
});
