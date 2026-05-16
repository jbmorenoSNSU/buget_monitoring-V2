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
        $query = Transaction::with(['account', 'category', 'transferToAccount'])
            ->orderBy('transaction_date', 'desc')->orderBy('id', 'desc');

        if (!empty($filters['type'])) {
            $query->byType($filters['type']);
        }
        if (!empty($filters['account_id'])) {
            $query->byAccount((int) $filters['account_id']);
        }
        if (!empty($filters['category_id'])) {
            $query->byCategory((int) $filters['category_id']);
        }
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->byDateRange($filters['date_from'], $filters['date_to']);
        }
        if (!empty($filters['search'])) {
            $query->where('description', 'like', '%' . $filters['search'] . '%');
        }

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

    public function getMonthlyIncome(int $month, int $year): float
    {
        return (float) Transaction::byType('income')->forMonth($month, $year)->sum('amount');
    }

    public function getMonthlyExpense(int $month, int $year): float
    {
        return (float) Transaction::byType('expense')->forMonth($month, $year)->sum('amount');
    }

    public function getRecentTransactions(int $limit = 10)
    {
        return Transaction::with(['account', 'category'])
            ->orderBy('transaction_date', 'desc')->orderBy('id', 'desc')
            ->limit($limit)->get();
    }
}
