<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\Debts\DebtDTO;
use App\Http\Requests\Debts\StoreDebtRequest;
use App\Http\Requests\Debts\UpdateDebtRequest;
use App\Interfaces\PersonRepositoryInterface;
use App\Models\Debt;
use App\Services\DebtService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DebtController extends Controller
{
    public function __construct(
        private DebtService $debtService,
        private PersonRepositoryInterface $personRepository
    ) {}

    public function index(Request $request): Response
    {
        $person_id = $request->filled('person_id') ? (int) $request->get('person_id') : null;

        return Inertia::render('Debts/Index', [
            'debts' => $this->debtService->paginate($person_id),
            'filters' => [
                'persons' => $this->personRepository->all_active(),
                'selectedPersonId' => $person_id,
            ],
        ]);
    }

    public function store(StoreDebtRequest $request): RedirectResponse
    {
        $this->debtService->create(DebtDTO::fromRequest($request->validated()));

        return redirect()->back()->with('success', 'Debt tracker created successfully.');
    }

    public function update(UpdateDebtRequest $request, Debt $debt): RedirectResponse
    {
        $this->debtService->update($debt, DebtDTO::fromRequest($request->validated()));

        return redirect()->back()->with('success', 'Debt tracker updated successfully.');
    }

    public function destroy(Debt $debt): RedirectResponse
    {
        $this->debtService->delete($debt);

        return redirect()->back()->with('success', 'Debt tracker deleted successfully.');
    }
}
