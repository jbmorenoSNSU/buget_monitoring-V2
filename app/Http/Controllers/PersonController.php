<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonRequest;
use App\Http\Resources\PersonResource;
use App\Models\Person;
use App\Services\PersonService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PersonController extends Controller
{
    public function __construct(private PersonService $service) {}

    public function index(): Response
    {
        return Inertia::render('Persons/Index', [
            'persons' => PersonResource::collection($this->service->getAll()),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Persons/Form');
    }

    public function store(StorePersonRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());
        return redirect()->route('persons.index')->with('success', 'Person created successfully.');
    }

    public function edit(Person $person): Response
    {
        return Inertia::render('Persons/Form', [
            'person' => new PersonResource($person),
        ]);
    }

    public function update(StorePersonRequest $request, Person $person): RedirectResponse
    {
        $this->service->update($person, $request->validated());
        return redirect()->route('persons.index')->with('success', 'Person updated successfully.');
    }

    public function destroy(Person $person): RedirectResponse
    {
        if (!$this->service->canDelete($person)) {
            return redirect()->route('persons.index')->with('error', 'Cannot delete person with linked accounts. Reassign their accounts first.');
        }
        $this->service->delete($person);
        return redirect()->route('persons.index')->with('success', 'Person deleted successfully.');
    }
}
