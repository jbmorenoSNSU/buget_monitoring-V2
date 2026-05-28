<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\TransactionRepositoryInterface;
use App\Models\Transaction;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service class handling core business operations for individual transactions.
 */
class TransactionService
{
    /**
     * Create a new TransactionService instance.
     *
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository
    ) {}

    /**
     * Get paginated transactions matching selected filters.
     *
     * @param array<string, mixed> $filters
     * @param int $per_page
     * @return CursorPaginator
     */
    public function get_paginated(array $filters = [], int $per_page = 15): CursorPaginator
    {
        if (!empty($filters['per_page'])) {
            $per_page = (int) $filters['per_page'];
        }

        return $this->transactionRepository->paginate($filters, $per_page);
    }

    /**
     * Create a new transaction.
     *
     * @param array<string, mixed> $data
     * @return Transaction
     */
    public function create(array $data): Transaction
    {
        return $this->transactionRepository->create($data);
    }

    /**
     * Update an existing transaction.
     *
     * @param Transaction $transaction
     * @param array<string, mixed> $data
     * @return Transaction
     */
    public function update(Transaction $transaction, array $data): Transaction
    {
        return $this->transactionRepository->update($transaction, $data);
    }

    /**
     * Delete a transaction.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function delete(Transaction $transaction): void
    {
        $this->transactionRepository->delete($transaction);
    }

    /**
     * Get the sum of all income transactions for a specific month/year.
     *
     * @param int $month
     * @param int $year
     * @param int|null $person_id
     * @return float
     */
    public function get_monthly_income(int $month, int $year, ?int $person_id = null): float
    {
        return $this->transactionRepository->monthly_sum('income', $month, $year, $person_id);
    }

    /**
     * Get the sum of all expense transactions for a specific month/year.
     *
     * @param int $month
     * @param int $year
     * @param int|null $person_id
     * @return float
     */
    public function get_monthly_expense(int $month, int $year, ?int $person_id = null): float
    {
        return $this->transactionRepository->monthly_sum('expense', $month, $year, $person_id);
    }

    /**
     * Get the most recent transactions.
     *
     * @param int $limit
     * @param int|null $person_id
     * @return Collection<int, Transaction>
     */
    public function get_recent_transactions(int $limit = 10, ?int $person_id = null): Collection
    {
        return $this->transactionRepository->recent($limit, $person_id);
    }
}
