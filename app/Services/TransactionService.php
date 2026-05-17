<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        // Handle custom per-page limits from filters
        if (!empty($filters['per_page'])) {
            $perPage = (int) $filters['per_page'];
        }

        $query = Transaction::with(['account.person', 'category', 'transferToAccount'])
            ->select('transactions.*'); // Critical: only select transaction fields to prevent join overlaps

        if (!empty($filters['type'])) {
            $query->byType($filters['type']);
        }
        if (!empty($filters['account_id'])) {
            $query->byAccount((int) $filters['account_id']);
        }
        if (!empty($filters['person_id'])) {
            $query->whereHas('account', fn ($q) => $q->where('person_id', (int) $filters['person_id']));
        }
        if (!empty($filters['category_id'])) {
            $query->byCategory((int) $filters['category_id']);
        }
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->byDateRange($filters['date_from'], $filters['date_to']);
        }
        if (!empty($filters['search'])) {
            $query->where('transactions.description', 'like', '%' . $filters['search'] . '%');
        }

        // Whitelisted dynamic sorting
        $sortBy = $filters['sort_by'] ?? 'transaction_date';
        $sortDirection = isset($filters['sort_direction']) && strtolower($filters['sort_direction']) === 'asc' ? 'asc' : 'desc';

        $allowedSorts = [
            'transaction_date' => 'transactions.transaction_date',
            'description' => 'transactions.description',
            'type' => 'transactions.type',
            'amount' => 'transactions.amount',
            'account' => 'accounts.name',
            'category' => 'categories.name',
        ];

        if (array_key_exists($sortBy, $allowedSorts)) {
            $dbField = $allowedSorts[$sortBy];
            if ($sortBy === 'account') {
                $query->leftJoin('accounts', 'transactions.account_id', '=', 'accounts.id');
            } elseif ($sortBy === 'category') {
                $query->leftJoin('categories', 'transactions.category_id', '=', 'categories.id');
            }
            $query->orderBy($dbField, $sortDirection);
        } else {
            $query->orderBy('transactions.transaction_date', 'desc');
        }

        // Secondary stable order to guarantee correct pagination sequence
        $query->orderBy('transactions.id', 'desc');

        return $query->paginate($perPage)->withQueryString();
    }


    public function create(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $transaction = Transaction::create($data);
            $this->applyBalanceEffect($transaction);
            return $transaction;
        });
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            $this->reverseBalanceEffect($transaction);
            $transaction->update($data);
            $transaction->refresh();
            $this->applyBalanceEffect($transaction);
            return $transaction;
        });
    }

    public function delete(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $this->reverseBalanceEffect($transaction);
            $transaction->delete();
        });
    }

    private function applyBalanceEffect(Transaction $transaction): void
    {
        $account = Account::find($transaction->account_id);
        $type = $transaction->type->value ?? $transaction->type;

        match ($type) {
            'income' => $account->increment('current_balance', (float)$transaction->amount),
            'expense' => $account->decrement('current_balance', (float)$transaction->amount),
            'transfer' => (function () use ($account, $transaction) {
                $account->decrement('current_balance', (float)$transaction->amount);
                if ($transaction->transfer_to_account_id) {
                    Account::find($transaction->transfer_to_account_id)
                        ->increment('current_balance', (float)$transaction->amount);
                }
            })(),
        };
    }

    private function reverseBalanceEffect(Transaction $transaction): void
    {
        $account = Account::find($transaction->account_id);
        $type = $transaction->type->value ?? $transaction->type;

        match ($type) {
            'income' => $account->decrement('current_balance', (float)$transaction->amount),
            'expense' => $account->increment('current_balance', (float)$transaction->amount),
            'transfer' => (function () use ($account, $transaction) {
                $account->increment('current_balance', (float)$transaction->amount);
                if ($transaction->transfer_to_account_id) {
                    Account::find($transaction->transfer_to_account_id)
                        ->decrement('current_balance', (float)$transaction->amount);
                }
            })(),
        };
    }

    public function getMonthlyIncome(int $month, int $year, ?int $personId = null): float
    {
        $query = Transaction::byType('income')->forMonth($month, $year);
        if ($personId) {
            $query->whereHas('account', fn ($q) => $q->where('person_id', $personId));
        }
        return (float) $query->sum('amount');
    }

    public function getMonthlyExpense(int $month, int $year, ?int $personId = null): float
    {
        $query = Transaction::byType('expense')->forMonth($month, $year);
        if ($personId) {
            $query->whereHas('account', fn ($q) => $q->where('person_id', $personId));
        }
        return (float) $query->sum('amount');
    }

    public function getRecentTransactions(int $limit = 10, ?int $personId = null)
    {
        $query = Transaction::with(['account.person', 'category'])
            ->orderBy('transaction_date', 'desc')->orderBy('id', 'desc');
        if ($personId) {
            $query->whereHas('account', fn ($q) => $q->where('person_id', $personId));
        }
        return $query->limit($limit)->get();
    }
}
