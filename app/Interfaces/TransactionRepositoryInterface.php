<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Transaction;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Collection;

interface TransactionRepositoryInterface
{
    public function paginate(array $filters, int $per_page): CursorPaginator;

    public function find(int $id): ?Transaction;

    public function create(array $data): Transaction;

    public function update(Transaction $transaction, array $data): Transaction;

    public function delete(Transaction $transaction): void;

    public function monthly_sum(string $type, int $month, int $year, ?int $person_id = null): float;

    public function recent(int $limit, ?int $person_id = null): Collection;

    public function sum_by_account_and_type(int $accountId, string $type, bool $isTransferTo = false): float;

    public function spent_by_category_map(int $month, int $year, ?int $person_id = null): array;

    public function spent_by_category_and_person_map(int $month, int $year): array;

    public function income_vs_expense_raw(string $from, string $to, ?int $person_id = null): Collection;

    public function category_expense_raw(int $month, int $year, ?int $person_id = null): Collection;

    public function account_statement_raw(int $account_id, string $from, string $to): Collection;

    public function compute_opening_balance(int $account_id, string $before, float $initial_balance): float;

    public function expense_by_date_range(string $from, string $to, ?int $person_id = null): Collection;

    public function calendar_transactions(string $start, string $end, ?int $person_id = null, ?int $account_id = null): Collection;

    /**
     * Sum income and expense for non-recurring transactions in a date range.
     *
     * @return array{income: float, expense: float}
     */
    public function non_recurring_net_for_projection(string $from, string $to): array;

    /**
     * Fetch all income and expense transactions for a year with category relations
     * for the Year-in-Review report.
     *
     * @return Collection<int, Transaction>
     */
    public function year_in_review_raw(string $from, string $to): Collection;
}
