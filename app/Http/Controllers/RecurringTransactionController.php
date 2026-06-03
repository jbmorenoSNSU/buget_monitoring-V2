<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RecurringTransaction\CreateRecurringTransactionAction;
use App\Actions\RecurringTransaction\UpdateRecurringTransactionAction;
use App\DTOs\RecurringTransactionDTO;
use App\Http\Requests\StoreRecurringTransactionRequest;
use App\Interfaces\AccountRepositoryInterface;
use App\Interfaces\CategoryRepositoryInterface;
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
        private AccountRepositoryInterface $accountRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private CreateRecurringTransactionAction $createRecurring,
        private UpdateRecurringTransactionAction $updateRecurring,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Recurring/Index', [
            'recurring' => $this->service->get_all(),
            'accounts' => $this->accountRepository->all_active(),
            'categories' => $this->categoryRepository->all_active(),
        ]);
    }



    public function store(StoreRecurringTransactionRequest $request): RedirectResponse
    {
        $this->authorize('create', RecurringTransaction::class);
        $this->createRecurring->execute(RecurringTransactionDTO::fromArray($request->validated()));

        return redirect()->route('recurring.index')->with('success', 'Recurring transaction created successfully.');
    }



    public function update(StoreRecurringTransactionRequest $request, RecurringTransaction $recurring): RedirectResponse
    {
        $this->authorize('update', $recurring);
        $this->updateRecurring->execute($recurring, RecurringTransactionDTO::fromArray($request->validated()));

        return redirect()->route('recurring.index')->with('success', 'Recurring transaction updated successfully.');
    }

    public function destroy(RecurringTransaction $recurring): RedirectResponse
    {
        $this->authorize('delete', $recurring);
        $this->service->delete($recurring);

        return redirect()->route('recurring.index')->with('success', 'Recurring transaction deleted successfully.');
    }

    public function toggle(RecurringTransaction $recurring): RedirectResponse
    {
        $this->authorize('update', $recurring);
        $this->service->toggle($recurring);
        $status = $recurring->fresh()->is_active ? 'activated' : 'paused';

        return redirect()->route('recurring.index')->with('success', "Recurring transaction {$status}.");
    }

    public function generate_now(): RedirectResponse
    {
        $this->authorize('create', RecurringTransaction::class);
        $count = $this->service->generate_due();

        return redirect()->route('recurring.index')->with('success', "{$count} recurring transaction(s) generated.");
    }
}
