<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecurringTransactionRequest;
use App\Interfaces\AccountRepositoryInterface;
use App\Interfaces\CategoryRepositoryInterface;
use App\Interfaces\DebtRepositoryInterface;
use App\Models\RecurringTransaction;
use App\Http\Resources\RecurringTransactionResource;
use App\Services\RecurringTransactionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles HTTP actions for recurring transaction scheduling.
 */
class RecurringTransactionController extends Controller
{
    public function __construct(
        private RecurringTransactionService $service,
        private AccountRepositoryInterface $accountRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private DebtRepositoryInterface $debtRepository
    ) {}

    /**
     * Display a listing of recurring transactions.
     */
    public function index(): Response
    {
        return Inertia::render('Recurring/Index', [
            'recurring' => RecurringTransactionResource::collection($this->service->get_all())->resolve(),
            'accounts' => $this->accountRepository->all_active(),
            'categories' => $this->categoryRepository->all_active(),
            'debts' => $this->debtRepository->all(),
        ]);
    }

    /**
     * Store a newly created recurring transaction.
     */
    public function store(StoreRecurringTransactionRequest $request): RedirectResponse
    {
        $this->authorize('create', RecurringTransaction::class);
        $this->service->create($request->validated());

        return redirect()->back()->with('success', 'Recurring transaction created successfully.');
    }

    /**
     * Update the specified recurring transaction.
     */
    public function update(StoreRecurringTransactionRequest $request, RecurringTransaction $recurring): RedirectResponse
    {
        $this->authorize('update', $recurring);
        $this->service->update($recurring, $request->validated());

        return redirect()->back()->with('success', 'Recurring transaction updated successfully.');
    }

    /**
     * Remove the specified recurring transaction.
     */
    public function destroy(RecurringTransaction $recurring): RedirectResponse
    {
        $this->authorize('delete', $recurring);
        $this->service->delete($recurring);

        return redirect()->back()->with('success', 'Recurring transaction deleted successfully.');
    }

    /**
     * Toggle the active/paused status of a recurring transaction.
     */
    public function toggle(RecurringTransaction $recurring): RedirectResponse
    {
        $this->authorize('update', $recurring);
        $this->service->toggle($recurring);
        $status = $recurring->fresh()->is_active ? 'activated' : 'paused';

        return redirect()->back()->with('success', "Recurring transaction {$status}.");
    }

    /**
     * Manually trigger generation of all due recurring transactions.
     */
    public function generate_now(): RedirectResponse
    {
        $this->authorize('create', RecurringTransaction::class);
        $count = $this->service->generate_due();

        return redirect()->back()->with('success', "{$count} recurring transaction(s) generated.");
    }
}
