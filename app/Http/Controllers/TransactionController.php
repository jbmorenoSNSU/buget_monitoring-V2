<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Interfaces\AccountRepositoryInterface;
use App\Interfaces\CategoryRepositoryInterface;
use App\Interfaces\DebtRepositoryInterface;
use App\Interfaces\PersonRepositoryInterface;
use App\Interfaces\TransactionRepositoryInterface;
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
        private TransactionRepositoryInterface $repository,
        private AccountRepositoryInterface $accountRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private PersonRepositoryInterface $personRepository,
        private DebtRepositoryInterface $debtRepository
    ) {}

    /**
     * Display a paginated, filterable listing of transactions.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only([
            'type', 'account_id', 'category_id', 'person_id', 'date_from', 'date_to', 'search',
            'sort_by', 'sort_direction', 'per_page', 'action',
        ]);

        return Inertia::render('Transactions/Index', [
            'transactions' => TransactionResource::collection($this->service->get_paginated($filters)),
            'filters' => $filters,
            'accounts' => Inertia::defer(fn () => $this->accountRepository->all_active(), 'form-data'),
            'categories' => Inertia::defer(fn () => $this->categoryRepository->all_active(), 'form-data'),
            'persons' => Inertia::defer(fn () => $this->personRepository->all_active(), 'form-data'),
            'debts' => Inertia::defer(fn () => $this->debtRepository->all(), 'form-data'),
        ]);
    }

    /**
     * Store a newly created transaction.
     */
    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $this->authorize('create', Transaction::class);
        $this->repository->create($request->validated());

        return redirect()->back()->with('success', 'Transaction created successfully.');
    }

    /**
     * Update the specified transaction.
     */
    public function update(StoreTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);
        $this->repository->update($transaction, $request->validated());

        return redirect()->back()->with('success', 'Transaction updated successfully.');
    }

    /**
     * Remove the specified transaction.
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);
        $this->repository->delete($transaction);

        return redirect()->back()->with('success', 'Transaction deleted successfully.');
    }
}
