<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Person\CreatePersonAction;
use App\Actions\Person\UpdatePersonAction;
use App\DTOs\PersonDTO;
use App\Http\Requests\StorePersonRequest;
use App\Http\Resources\PersonResource;
use App\Models\Person;
use App\Services\PersonService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles HTTP actions for person profile management.
 */
class PersonController extends Controller
{
    public function __construct(
        private PersonService $service,
        private CreatePersonAction $createPerson,
        private UpdatePersonAction $updatePerson,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Persons/Index', [
            'persons' => PersonResource::collection($this->service->get_all()),
        ]);
    }

    public function store(StorePersonRequest $request): RedirectResponse
    {
        $this->authorize('create', Person::class);
        $this->createPerson->execute(PersonDTO::fromArray($request->validated()));

        return redirect()->route('persons.index')->with('success', 'Person created successfully.');
    }

    public function update(StorePersonRequest $request, Person $person): RedirectResponse
    {
        $this->authorize('update', $person);
        $this->updatePerson->execute($person, PersonDTO::fromArray($request->validated()));

        return redirect()->route('persons.index')->with('success', 'Person updated successfully.');
    }

    public function destroy(Person $person): RedirectResponse
    {
        $this->authorize('delete', $person);
        if (! $this->service->can_delete($person)) {
            return redirect()->route('persons.index')->with('error', 'Cannot delete person with linked accounts. Reassign their accounts first.');
        }
        $this->service->delete($person);

        return redirect()->route('persons.index')->with('success', 'Person deleted successfully.');
    }
}
