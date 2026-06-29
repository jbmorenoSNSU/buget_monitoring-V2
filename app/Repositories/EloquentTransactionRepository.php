<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\DebtRepositoryInterface;
use App\Interfaces\TransactionRepositoryInterface;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    public function __construct(
        private DebtRepositoryInterface $debtRepository
    ) {}

    public function paginate(array $filters, int $per_page): CursorPaginator
    {
        $query = Transaction::with([
            'account:id,name,person_id',
            'account.person:id,name,color',
            'category:id,name,icon,color',
            'transferToAccount:id,name',
        ])
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
            $pid = (int) $filters['person_id'];
            $query->where(function ($q) use ($pid) {
                $q->whereHas('account', fn ($sq) => $sq->where('person_id', $pid))
                    ->orWhereHas('transferToAccount', fn ($sq) => $sq->where('person_id', $pid));
            });
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

    /**
     * Create a new transaction and automatically adjust the linked account's balance.
     * We wrap this in a database "transaction" so that if the balance update fails,
     * the new transaction record isn't saved either (preventing ghost money).
     */
    public function create(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $transaction = Transaction::create($data);
            $this->apply_balance_effect($transaction);

            return $transaction;
        });
    }

    /**
     * Update an existing transaction. This requires careful math to ensure balances stay correct.
     */
    public function update(Transaction $transaction, array $data): Transaction
    {
        $temp = clone $transaction;
        $temp->fill($data);

        // These are core fields that change where the money is going or coming from.
        $criticalFields = ['account_id', 'type', 'transfer_to_account_id', 'debt_id'];

        // If the user changed the account or the type (e.g., Expense to Income),
        // the safest way to fix the math is to completely undo the old transaction's 
        // effect, save the new details, and apply the new effect.
        if ($temp->isDirty($criticalFields)) {
            return DB::transaction(function () use ($transaction, $data) {
                $this->reverse_balance_effect($transaction);
                $transaction->update($data);
                $transaction->refresh();
                $this->apply_balance_effect($transaction);

                return $transaction;
            });
        }

        // If the user only changed the amount (and not the accounts or type),
        // we can just calculate the difference and adjust the balances directly.
        return DB::transaction(function () use ($transaction, $data) {
            $transaction->loadMissing('account');

            $oldAmount = (float) $transaction->amount;
            $transaction->update($data);
            $newAmount = (float) $transaction->amount;

            $difference = $newAmount - $oldAmount;

            if ($difference != 0.0) {
                $type = $transaction->type->value ?? $transaction->type;
                $account = $transaction->account ?? Account::findOrFail($transaction->account_id);

                match ($type) {
                    'income' => $account->increment('current_balance', $difference),
                    'expense' => (function () use ($transaction, $account, $difference) {
                        $account->decrement('current_balance', $difference);
                        if ($transaction->debt_id) {
                            $this->debtRepository->decrement_principal((int) $transaction->debt_id, $difference);
                        }
                    })(),
                    'transfer' => (function () use ($transaction, $account, $difference) {
                        $account->decrement('current_balance', $difference);
                        if ($transaction->transfer_to_account_id) {
                            Account::findOrFail($transaction->transfer_to_account_id)
                                ->increment('current_balance', $difference);
                        }
                    })(),
                };
            }

            return $transaction;
        });
    }

    /**
     * Delete a transaction. Before removing the record, we must "undo" its effect
     * on the account's current balance so the money is returned to how it was.
     */
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
            $query->where(function ($q) use ($person_id) {
                $q->whereHas('account', fn ($sq) => $sq->where('person_id', $person_id))
                    ->orWhereHas('transferToAccount', fn ($sq) => $sq->where('person_id', $person_id));
            });
        }

        return (float) $query->sum('amount');
    }

    public function recent(int $limit, ?int $person_id = null): Collection
    {
        $query = Transaction::with([
            'account:id,name,person_id',
            'account.person:id,name,color',
            'category:id,name,icon,color',
        ])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc');
        if ($person_id) {
            $query->where(function ($q) use ($person_id) {
                $q->whereHas('account', fn ($sq) => $sq->where('person_id', $person_id))
                    ->orWhereHas('transferToAccount', fn ($sq) => $sq->where('person_id', $person_id));
            });
        }

        return $query->limit($limit)->get();
    }

    /**
     * Applies the financial effect of a transaction to the relevant accounts.
     * For example, an 'income' increases the balance, an 'expense' decreases it.
     */
    private function apply_balance_effect(Transaction $transaction): void
    {
        $account = $transaction->account ?? Account::findOrFail($transaction->account_id);
        $type = $transaction->type->value ?? $transaction->type;

        match ($type) {
            'income' => $account->increment('current_balance', (float) $transaction->amount),
            'expense' => (function () use ($transaction, $account) {
                $account->decrement('current_balance', (float) $transaction->amount);
                if ($transaction->debt_id) {
                    $this->debtRepository->decrement_principal($transaction->debt_id, (float) $transaction->amount);
                }
            })(),
            'transfer' => (function () use ($transaction, $account) {
                $account->decrement('current_balance', (float) $transaction->amount);
                if ($transaction->transfer_to_account_id) {
                    Account::findOrFail($transaction->transfer_to_account_id)
                        ->increment('current_balance', (float) $transaction->amount);
                }
            })(),
        };
    }

    /**
     * Undoes the financial effect of a transaction. This is the exact mathematical
     * opposite of apply_balance_effect. Used when deleting or radically changing a transaction.
     */
    private function reverse_balance_effect(Transaction $transaction): void
    {
        $account = $transaction->account ?? Account::findOrFail($transaction->account_id);
        $type = $transaction->type->value ?? $transaction->type;

        match ($type) {
            'income' => $account->decrement('current_balance', (float) $transaction->amount),
            'expense' => (function () use ($transaction, $account) {
                $account->increment('current_balance', (float) $transaction->amount);
                if ($transaction->debt_id) {
                    $this->debtRepository->increment_principal($transaction->debt_id, (float) $transaction->amount);
                }
            })(),
            'transfer' => (function () use ($transaction, $account) {
                $account->increment('current_balance', (float) $transaction->amount);
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
            $query->where(function ($q) use ($person_id) {
                $q->whereHas('account', fn ($sq) => $sq->where('person_id', $person_id))
                    ->orWhereHas('transferToAccount', fn ($sq) => $sq->where('person_id', $person_id));
            });
        }

        return $query->pluck('spent', 'category_id')->toArray();
    }

    public function spent_by_category_and_person_map(int $month, int $year): array
    {
        $query = Transaction::join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->where('transactions.type', 'expense')
            ->whereMonth('transactions.transaction_date', $month)
            ->whereYear('transactions.transaction_date', $year)
            ->whereNotNull('accounts.person_id')
            ->selectRaw('transactions.category_id, accounts.person_id, SUM(transactions.amount) as spent')
            ->groupBy('transactions.category_id', 'accounts.person_id')
            ->get();

        $map = [];
        foreach ($query as $row) {
            if (! isset($map[$row->person_id])) {
                $map[$row->person_id] = [];
            }
            $map[$row->person_id][$row->category_id] = (float) $row->spent;
        }

        return $map;
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
            $query->where(function ($q) use ($person_id) {
                $q->whereHas('account', fn ($sq) => $sq->where('person_id', $person_id))
                    ->orWhereHas('transferToAccount', fn ($sq) => $sq->where('person_id', $person_id));
            });
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
            $query->where(function ($q) use ($person_id) {
                $q->whereHas('account', fn ($sq) => $sq->where('person_id', $person_id))
                    ->orWhereHas('transferToAccount', fn ($sq) => $sq->where('person_id', $person_id));
            });
        }

        return $query->with('category:id,name,color,icon')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->get();
    }

    public function account_statement_raw(int $account_id, string $from, string $to): Collection
    {
        return Transaction::with('category:id,name,icon,color')
            ->select(['id', 'account_id', 'category_id', 'type', 'amount', 'transaction_date', 'description', 'notes', 'reference_number', 'transfer_to_account_id'])
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
                COALESCE(SUM(CASE WHEN type = \'income\' AND account_id = ? THEN amount ELSE 0 END), 0) as income,
                COALESCE(SUM(CASE WHEN type = \'expense\' AND account_id = ? THEN amount ELSE 0 END), 0) as expense,
                COALESCE(SUM(CASE WHEN type = \'transfer\' AND account_id = ? THEN amount ELSE 0 END), 0) as transfer_out,
                COALESCE(SUM(CASE WHEN type = \'transfer\' AND transfer_to_account_id = ? THEN amount ELSE 0 END), 0) as transfer_in
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
            $query->where(function ($q) use ($person_id) {
                $q->whereHas('account', fn ($sq) => $sq->where('person_id', $person_id))
                    ->orWhereHas('transferToAccount', fn ($sq) => $sq->where('person_id', $person_id));
            });
        }

        return $query->orderBy('transaction_date')->get();
    }

    public function calendar_transactions(string $start, string $end, ?int $person_id = null, ?int $account_id = null): Collection
    {
        $query = Transaction::with(['category:id,name,icon,color', 'account:id,name'])
            ->select(['id', 'account_id', 'category_id', 'type', 'amount', 'transaction_date', 'description', 'transfer_to_account_id'])
            ->whereBetween('transaction_date', [$start, $end]);

        if ($account_id) {
            $query->where(function ($q) use ($account_id) {
                $q->where('account_id', $account_id)
                    ->orWhere('transfer_to_account_id', $account_id);
            });
        } elseif ($person_id) {
            $query->where(function ($q) use ($person_id) {
                $q->whereHas('account', fn ($sq) => $sq->where('person_id', $person_id))
                    ->orWhereHas('transferToAccount', fn ($sq) => $sq->where('person_id', $person_id));
            });
        }

        return $query->get();
    }

    public function for_debt(int $debt_id): Collection
    {
        return Transaction::with([
            'account:id,name,color,person_id',
            'account.person:id,name,color',
            'category:id,name,icon,color',
        ])
            ->where('debt_id', $debt_id)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();
    }

    /**
     * Sum income and expense for non-recurring transactions in a date range.
     *
     * @return array{income: float, expense: float}
     */
    public function non_recurring_net_for_projection(string $from, string $to): array
    {
        $rows = Transaction::select('type', DB::raw('SUM(amount) as total'))
            ->whereBetween('transaction_date', [$from, $to])
            ->whereIn('type', ['income', 'expense'])
            ->whereNull('recurring_id')
            ->groupBy('type')
            ->get();

        $income = (float) $rows->where('type', 'income')->value('total');
        $expense = (float) $rows->where('type', 'expense')->value('total');

        return compact('income', 'expense');
    }

    public function year_in_review_totals(string $from, string $to): array
    {
        $totals = Transaction::select('type', DB::raw('SUM(amount) as total'))
            ->whereBetween('transaction_date', [$from, $to])
            ->whereIn('type', ['income', 'expense'])
            ->groupBy('type')
            ->get();

        return [
            'income' => (float) $totals->where('type', 'income')->value('total'),
            'expense' => (float) $totals->where('type', 'expense')->value('total'),
        ];
    }

    public function year_in_review_top_categories(string $from, string $to, int $limit = 5): array
    {
        return Transaction::with('category:id,name,icon,color')
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->whereBetween('transaction_date', [$from, $to])
            ->where('type', 'expense')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'category_name' => $row->category?->name ?? 'Unknown',
                'category_icon' => $row->category?->icon ?? 'tag',
                'category_color' => $row->category?->color ?? '#94A3B8',
                'amount' => (float) $row->total,
            ])
            ->toArray();
    }

    public function year_in_review_busiest_month(string $from, string $to): array
    {
        $driver = DB::connection()->getDriverName();
        $month_expr = $driver === 'sqlite' ? "strftime('%m', transaction_date)" : 'MONTH(transaction_date)';

        $busiest = Transaction::select(DB::raw("$month_expr as month"), DB::raw('SUM(amount) as total'))
            ->whereBetween('transaction_date', [$from, $to])
            ->where('type', 'expense')
            ->groupBy('month')
            ->orderByDesc('total')
            ->first();

        return [
            'month' => $busiest ? (int) $busiest->month : null,
            'amount' => $busiest ? (float) $busiest->total : 0.0,
        ];
    }
}
