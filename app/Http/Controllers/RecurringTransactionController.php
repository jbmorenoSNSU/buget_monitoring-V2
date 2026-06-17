<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecurringTransactionRequest;
use App\Interfaces\AccountRepositoryInterface;
use App\Interfaces\CategoryRepositoryInterface;
use App\Interfaces\DebtRepositoryInterface;
use App\Interfaces\RecurringTransactionRepositoryInterface;
use App\Models\RecurringTransaction;
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
        private RecurringTransactionRepositoryInterface $repository,
        private AccountRepositoryInterface $accountRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private DebtRepositoryInterface $debtRepository
    ) {}

    public function index(): Response
    {
        return Inertia::render('Recurring/Index', [
            'recurring' => $this->service->get_all(),
            'accounts' => $this->accountRepository->all_active(),
            'categories' => $this->categoryRepository->all_active(),
            'debts' => $this->debtRepository->all(),
        ]);
    }

    public function store(StoreRecurringTransactionRequest $request): RedirectResponse
    {
        $this->authorize('create', RecurringTransaction::class);
        $this->repository->create($request->validated());

        return redirect()->back()->with('success', 'Recurring transaction created successfully.');
    }

    public function update(StoreRecurringTransactionRequest $request, RecurringTransaction $recurring): RedirectResponse
    {
        $this->authorize('update', $recurring);
        $this->repository->update($recurring, $request->validated());

        return redirect()->back()->with('success', 'Recurring transaction updated successfully.');
    }

    public function destroy(RecurringTransaction $recurring): RedirectResponse
    {
        $this->authorize('delete', $recurring);
        $this->service->delete($recurring);

        return redirect()->back()->with('success', 'Recurring transaction deleted successfully.');
    }

    public function toggle(RecurringTransaction $recurring): RedirectResponse
    {
        $this->authorize('update', $recurring);
        $this->service->toggle($recurring);
        $status = $recurring->fresh()->is_active ? 'activated' : 'paused';

        return redirect()->back()->with('success', "Recurring transaction {$status}.");
    }

    public function generate_now(): RedirectResponse
    {
        $this->authorize('create', RecurringTransaction::class);
        $count = $this->service->generate_due();

        return redirect()->back()->with('success', "{$count} recurring transaction(s) generated.");
    }
}
