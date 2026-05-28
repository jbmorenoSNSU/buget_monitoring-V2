<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Transaction\CreateTransactionAction;
use App\Actions\Transaction\UpdateTransactionAction;
use App\DTOs\TransactionDTO;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Interfaces\AccountRepositoryInterface;
use App\Interfaces\CategoryRepositoryInterface;
use App\Interfaces\PersonRepositoryInterface;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles HTTP actions for financial transaction management.
 */
class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $service,
        private AccountRepositoryInterface $accountRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private PersonRepositoryInterface $personRepository,
        private CreateTransactionAction $createTransaction,
        private UpdateTransactionAction $updateTransaction,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only([
            'type', 'account_id', 'category_id', 'person_id', 'date_from', 'date_to', 'search',
            'sort_by', 'sort_direction', 'per_page',
        ]);

        return Inertia::render('Transactions/Index', [
            'transactions' => TransactionResource::collection($this->service->get_paginated($filters)),
            'filters'      => $filters,
            'accounts'     => $this->accountRepository->all_active(),
            'categories'   => $this->categoryRepository->all_active(),
            'persons'      => $this->personRepository->all_active(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Transactions/Form', [
            'accounts'   => $this->accountRepository->all_active(),
            'categories' => $this->categoryRepository->all_active(),
        ]);
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $this->authorize('create', Transaction::class);
        $this->createTransaction->execute(TransactionDTO::fromArray($request->validated()));
        return redirect()->route('transactions.index')->with('success', 'Transaction created successfully.');
    }

    public function edit(Transaction $transaction): Response
    {
        return Inertia::render('Transactions/Form', [
            'transaction' => new TransactionResource($transaction->load(['account:id,name', 'category:id,name,icon,color', 'transferToAccount:id,name'])),
            'accounts'    => $this->accountRepository->all_active(),
            'categories'  => $this->categoryRepository->all_active(),
        ]);
    }

    public function update(StoreTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);
        $this->updateTransaction->execute($transaction, TransactionDTO::fromArray($request->validated()));
        return redirect()->route('transactions.index')->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);
        $this->service->delete($transaction);
        return redirect()->route('transactions.index')->with('success', 'Transaction deleted successfully.');
    }
}
