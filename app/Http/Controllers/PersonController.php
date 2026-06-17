<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonRequest;
use App\Http\Resources\PersonResource;
use App\Interfaces\PersonRepositoryInterface;
use App\Models\Person;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles HTTP actions for person profile management.
 */
class PersonController extends Controller
{
    public function __construct(
        private PersonRepositoryInterface $repository
    ) {}

    public function index(): Response
    {
        return Inertia::render('Persons/Index', [
            'persons' => PersonResource::collection($this->repository->all()),
        ]);
    }

    public function store(StorePersonRequest $request): RedirectResponse
    {
        $this->authorize('create', Person::class);
        $this->repository->create($request->validated());

        return redirect()->back()->with('success', 'Person created successfully.');
    }

    public function update(StorePersonRequest $request, Person $person): RedirectResponse
    {
        $this->authorize('update', $person);
        $this->repository->update($person, $request->validated());

        return redirect()->back()->with('success', 'Person updated successfully.');
    }

    public function destroy(Person $person): RedirectResponse
    {
        $this->authorize('delete', $person);
        if ($this->repository->has_accounts($person)) {
            return redirect()->back()->with('error', 'Cannot delete person with linked accounts. Reassign their accounts first.');
        }
        $this->repository->delete($person);

        return redirect()->back()->with('success', 'Person deleted successfully.');
    }
}
