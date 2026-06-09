<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\TransactionRepositoryInterface;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    public function paginate(array $filters, int $per_page): CursorPaginator
    {
        $query = Transaction::with(['account.person:id,name,color', 'category:id,name,icon,color', 'transferToAccount:id,name'])
            ->select([
                'transactions.id',
                'transactions.account_id',
                'transactions.category_id',
                'transactions.type',
                'transactions.amount',
                'transactions.transaction_date',
                'transactions.description',
                'transactions.notes',
                'transactions.reference_number',
                'transactions.transfer_to_account_id',
                'transactions.recurring_id',
                'transactions.created_at',
                'transactions.updated_at',
            ]);

        if (! empty($filters['type'])) {
            $query->byType($filters['type']);
        }
        if (! empty($filters['account_id'])) {
            $query->byAccount((int) $filters['account_id']);
        }
        if (! empty($filters['person_id'])) {
            $query->whereHas('account', fn ($q) => $q->where('person_id', (int) $filters['person_id']));
        }
        if (! empty($filters['category_id'])) {
            $query->byCategory((int) $filters['category_id']);
        }
        if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
            $query->byDateRange($filters['date_from'], $filters['date_to']);
        }
        if (! empty($filters['search'])) {
            $query->where('transactions.description', 'like', $filters['search'].'%');
        }

        $allowed_sorts = [
            'transaction_date' => 'transactions.transaction_date',
            'description' => 'transactions.description',
            'type' => 'transactions.type',
            'amount' => 'transactions.amount',
            'account' => 'transactions.account_id',
            'category' => 'transactions.category_id',
        ];

        $sort_by = $filters['sort_by'] ?? 'transaction_date';
        $sort_direction = isset($filters['sort_direction']) && strtolower($filters['sort_direction']) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($allowed_sorts[$sort_by] ?? 'transactions.transaction_date', $sort_direction);
        $query->orderBy('transactions.id', 'desc');

        return $query->cursorPaginate($per_page)->withQueryString();
    }

    public function find(int $id): ?Transaction
    {
        return Transaction::with(['account:id,name', 'category:id,name,icon,color', 'transferToAccount:id,name'])->find($id);
    }

    public function create(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $transaction = Transaction::create($data);
            $this->apply_balance_effect($transaction);

            return $transaction;
        });
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            $this->reverse_balance_effect($transaction);
            $transaction->update($data);
            $transaction->refresh();
            $this->apply_balance_effect($transaction);

            return $transaction;
        });
    }

    public function delete(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $this->reverse_balance_effect($transaction);
            $transaction->delete();
        });
    }

    public function monthly_sum(string $type, int $month, int $year, ?int $person_id = null): float
    {
        $query = Transaction::byType($type)->forMonth($month, $year);
        if ($person_id) {
            $query->whereHas('account', fn ($q) => $q->where('person_id', $person_id));
        }

        return (float) $query->sum('amount');
    }

    public function recent(int $limit, ?int $person_id = null): Collection
    {
        $query = Transaction::with(['account.person:id,name,color', 'category:id,name,icon,color'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc');
        if ($person_id) {
            $query->whereHas('account', fn ($q) => $q->where('person_id', $person_id));
        }

        return $query->limit($limit)->get();
    }

    private function apply_balance_effect(Transaction $transaction): void
    {
        $account = $transaction->account ?? Account::findOrFail($transaction->account_id);
        $type = $transaction->type->value ?? $transaction->type;

        match ($type) {
            'income' => $account->increment('current_balance', (float) $transaction->amount),
            'expense' => $account->decrement('current_balance', (float) $transaction->amount),
            'transfer' => (function () use ($transaction) {
                $transaction->account->decrement('current_balance', (float) $transaction->amount);
                if ($transaction->transfer_to_account_id) {
                    Account::findOrFail($transaction->transfer_to_account_id)
                        ->increment('current_balance', (float) $transaction->amount);
                }
            })(),
        };
    }

    private function reverse_balance_effect(Transaction $transaction): void
    {
        $account = $transaction->account ?? Account::findOrFail($transaction->account_id);
        $type = $transaction->type->value ?? $transaction->type;

        match ($type) {
            'income' => $account->decrement('current_balance', (float) $transaction->amount),
            'expense' => $account->increment('current_balance', (float) $transaction->amount),
            'transfer' => (function () use ($transaction) {
                $transaction->account->increment('current_balance', (float) $transaction->amount);
                if ($transaction->transfer_to_account_id) {
                    Account::findOrFail($transaction->transfer_to_account_id)
                        ->decrement('current_balance', (float) $transaction->amount);
                }
            })(),
        };
    }

    public function sum_by_account_and_type(int $accountId, string $type, bool $isTransferTo = false): float
    {
        $query = Transaction::query();
        if ($isTransferTo) {
            $query->where('transfer_to_account_id', $accountId);
        } else {
            $query->where('account_id', $accountId);
        }

        return (float) $query->where('type', $type)->sum('amount');
    }

    public function spent_by_category_map(int $month, int $year, ?int $person_id = null): array
    {
        $query = Transaction::where('type', 'expense')
            ->forMonth($month, $year)
            ->selectRaw('category_id, SUM(amount) as spent')
            ->groupBy('category_id');

        if ($person_id) {
            $query->whereHas('account', fn ($q) => $q->where('person_id', $person_id));
        }

        return $query->pluck('spent', 'category_id')->toArray();
    }

    public function income_vs_expense_raw(string $from, string $to, ?int $person_id = null): Collection
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $year_expr = "strftime('%Y', transaction_date)";
            $month_expr = "strftime('%m', transaction_date)";
        } else {
            $year_expr = 'YEAR(transaction_date)';
            $month_expr = 'MONTH(transaction_date)';
        }

        $query = Transaction::select(
            DB::raw("$year_expr as year"),
            DB::raw("$month_expr as month"),
            'type',
            DB::raw('SUM(amount) as total')
        )
            ->whereIn('type', ['income', 'expense'])
            ->whereBetween('transaction_date', [$from, $to]);

        if ($person_id) {
            $query->whereHas('account', fn ($q) => $q->where('person_id', $person_id));
        }

        return $query->groupBy('year', 'month', 'type')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }

    public function category_expense_raw(int $month, int $year, ?int $person_id = null): Collection
    {
        $query = Transaction::select('category_id', DB::raw('SUM(amount) as total'))
            ->where('type', 'expense')
            ->forMonth($month, $year);

        if ($person_id) {
            $query->whereHas('account', fn ($q) => $q->where('person_id', $person_id));
        }

        return $query->with('category:id,name,color,icon')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->get();
    }

    public function account_statement_raw(int $account_id, string $from, string $to): Collection
    {
        return Transaction::with('category:id,name,icon,color')
            ->where(function ($q) use ($account_id) {
                $q->where('account_id', $account_id)
                    ->orWhere('transfer_to_account_id', $account_id);
            })
            ->whereBetween('transaction_date', [$from, $to])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();
    }

    public function compute_opening_balance(int $account_id, string $before, float $initial_balance): float
    {
        $totals = Transaction::where('transaction_date', '<', $before)
            ->selectRaw('
                COALESCE(SUM(CASE WHEN type = "income" AND account_id = ? THEN amount ELSE 0 END), 0) as income,
                COALESCE(SUM(CASE WHEN type = "expense" AND account_id = ? THEN amount ELSE 0 END), 0) as expense,
                COALESCE(SUM(CASE WHEN type = "transfer" AND account_id = ? THEN amount ELSE 0 END), 0) as transfer_out,
                COALESCE(SUM(CASE WHEN type = "transfer" AND transfer_to_account_id = ? THEN amount ELSE 0 END), 0) as transfer_in
            ', [$account_id, $account_id, $account_id, $account_id])
            ->first();

        return $initial_balance + (float) $totals->income - (float) $totals->expense - (float) $totals->transfer_out + (float) $totals->transfer_in;
    }

    public function expense_by_date_range(string $from, string $to, ?int $person_id = null): Collection
    {
        $query = Transaction::select('transaction_date', 'amount')
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$from, $to]);

        if ($person_id) {
            $query->whereHas('account', fn ($q) => $q->where('person_id', $person_id));
        }

        return $query->orderBy('transaction_date')->get();
    }

    public function calendar_transactions(string $start, string $end): Collection
    {
        return Transaction::with(['category:id,name,icon,color', 'account:id,name'])
            ->whereBetween('transaction_date', [$start, $end])
            ->get();
    }

    public function split_transactions_raw(string $from, string $to): Collection
    {
        return Transaction::with(['account.person', 'splitWithPerson'])
            ->whereNotNull('split_with_person_id')
            ->whereBetween('transaction_date', [$from, $to])
            ->orderBy('transaction_date', 'desc')
            ->get();
    }
}
