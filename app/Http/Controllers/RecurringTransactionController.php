<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecurringTransactionRequest;
use App\Models\Account;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Services\RecurringTransactionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RecurringTransactionController extends Controller
{
    public function __construct(private RecurringTransactionService $service) {}

    public function index(): Response
    {
        return Inertia::render('Recurring/Index', [
            'recurring' => $this->service->getAll(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Recurring/Form', [
            'accounts' => Account::active()->orderBy('name')->get(),
            'categories' => Category::active()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreRecurringTransactionRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());
        return redirect()->route('recurring.index')->with('success', 'Recurring transaction created successfully.');
    }

    public function edit(RecurringTransaction $recurring): Response
    {
        return Inertia::render('Recurring/Form', [
            'recurring' => $recurring->load(['account', 'category']),
            'accounts' => Account::active()->orderBy('name')->get(),
            'categories' => Category::active()->orderBy('name')->get(),
        ]);
    }

    public function update(StoreRecurringTransactionRequest $request, RecurringTransaction $recurring): RedirectResponse
    {
        $this->service->update($recurring, $request->validated());
        return redirect()->route('recurring.index')->with('success', 'Recurring transaction updated successfully.');
    }

    public function destroy(RecurringTransaction $recurring): RedirectResponse
    {
        $this->service->delete($recurring);
        return redirect()->route('recurring.index')->with('success', 'Recurring transaction deleted successfully.');
    }

    public function toggle(RecurringTransaction $recurring): RedirectResponse
    {
        $this->service->toggle($recurring);
        $status = $recurring->fresh()->is_active ? 'activated' : 'paused';
        return redirect()->route('recurring.index')->with('success', "Recurring transaction {$status}.");
    }

    public function generateNow(): RedirectResponse
    {
        $count = $this->service->generateDue();
        return redirect()->route('recurring.index')->with('success', "{$count} recurring transaction(s) generated.");
    }
}
