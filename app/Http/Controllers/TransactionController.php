<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function __construct(private TransactionService $service) {}

    public function index(Request $request): Response
    {
        $filters = $request->only([
            'type', 'account_id', 'category_id', 'person_id', 'date_from', 'date_to', 'search',
            'sort_by', 'sort_direction', 'per_page'
        ]);
        $transactions = $this->service->getPaginated($filters);

        return Inertia::render('Transactions/Index', [
            'transactions' => TransactionResource::collection($transactions),
            'filters' => $filters,
            'accounts' => Account::active()->orderBy('name')->get(['id', 'name']),
            'categories' => Category::active()->orderBy('name')->get(['id', 'name', 'type']),
            'persons' => \App\Models\Person::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }


    public function create(): Response
    {
        return Inertia::render('Transactions/Form', [
            'accounts' => Account::active()->with('accountType')->orderBy('name')->get(),
            'categories' => Category::active()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());
        return redirect()->route('transactions.index')->with('success', 'Transaction created successfully.');
    }

    public function edit(Transaction $transaction): Response
    {
        return Inertia::render('Transactions/Form', [
            'transaction' => new TransactionResource($transaction->load(['account', 'category', 'transferToAccount'])),
            'accounts' => Account::active()->with('accountType')->orderBy('name')->get(),
            'categories' => Category::active()->orderBy('name')->get(),
        ]);
    }

    public function update(StoreTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->service->update($transaction, $request->validated());
        return redirect()->route('transactions.index')->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->service->delete($transaction);
        return redirect()->route('transactions.index')->with('success', 'Transaction deleted successfully.');
    }
}
