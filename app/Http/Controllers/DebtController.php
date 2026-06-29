<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Debts\StoreDebtRequest;
use App\Http\Requests\Debts\UpdateDebtRequest;
use App\Http\Resources\DebtResource;
use App\Http\Resources\TransactionResource;
use App\Interfaces\PersonRepositoryInterface;
use App\Interfaces\TransactionRepositoryInterface;
use App\Models\Debt;
use App\Services\DebtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles HTTP actions for debt tracker management.
 */
class DebtController extends Controller
{
    public function __construct(
        private DebtService $debtService,
        private PersonRepositoryInterface $personRepository
    ) {}

    /**
     * Display a listing of debts, optionally filtered by person.
     */
    public function index(Request $request): Response
    {
        $person_id = $request->filled('person_id') ? (int) $request->get('person_id') : null;

        return Inertia::render('Debts/Index', [
            'debts' => DebtResource::collection($this->debtService->paginate($person_id)),
            'filters' => [
                'persons' => $this->personRepository->all_active(),
                'selectedPersonId' => $person_id,
            ],
        ]);
    }

    /**
     * Store a newly created debt.
     */
    public function store(StoreDebtRequest $request): RedirectResponse
    {
        $this->authorize('create', Debt::class);
        $this->debtService->create($request->validated());

        return redirect()->back()->with('success', 'Debt tracker created successfully.');
    }

    /**
     * Update the specified debt.
     */
    public function update(UpdateDebtRequest $request, Debt $debt): RedirectResponse
    {
        $this->authorize('update', $debt);
        $this->debtService->update($debt, $request->validated());

        return redirect()->back()->with('success', 'Debt tracker updated successfully.');
    }

    /**
     * Remove the specified debt.
     */
    public function destroy(Debt $debt): RedirectResponse
    {
        $this->authorize('delete', $debt);
        $this->debtService->delete($debt);

        return redirect()->back()->with('success', 'Debt tracker deleted successfully.');
    }

    /**
     * Get the transaction history for a specific debt.
     */
    public function transactions(Debt $debt, TransactionRepositoryInterface $transactionRepo): JsonResponse
    {
        $this->authorize('view', $debt);

        $transactions = $transactionRepo->for_debt($debt->id);

        return response()->json(TransactionResource::collection($transactions));
    }
}
