<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\AccountRepositoryInterface;
use App\Interfaces\BudgetGoalRepositoryInterface;
use App\Interfaces\TransactionRepositoryInterface;
use Carbon\Carbon;

/**
 * Service class handling statement reports, budget goal comparisons, and calendar ledger aggregates.
 */
class StatementReportService
{
    /**
     * Create a new StatementReportService instance.
     */
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository,
        private BudgetGoalRepositoryInterface $budgetGoalRepository,
        private AccountRepositoryInterface $accountRepository
    ) {}

    /**
     * Compile a running statement for a specific financial account over a date range.
     *
     * @return array<string, mixed>
     */
    public function account_statement(int $account_id, string $from, string $to): array
    {
        $transactions = $this->transactionRepository->account_statement_raw($account_id, $from, $to);

        $account = $this->accountRepository->find($account_id);
        $initial_balance = $account ? (float) $account->initial_balance : 0.0;
        $opening_balance = $this->transactionRepository->compute_opening_balance($account_id, $from, $initial_balance);

        $running_balance = $opening_balance;
        $items = $transactions->map(function ($t) use (&$running_balance, $account_id) {
            $type = $t->type->value ?? $t->type;
            if ($t->transfer_to_account_id == $account_id && $type === 'transfer') {
                $running_balance += (float) $t->amount;
                $effective_type = 'income';
            } elseif ($type === 'income') {
                $running_balance += (float) $t->amount;
                $effective_type = 'income';
            } else {
                $running_balance -= (float) $t->amount;
                $effective_type = 'expense';
            }

            return [
                'date' => $t->transaction_date->format('Y-m-d'),
                'description' => $t->description,
                'category' => $t->category?->name ?? 'Transfer',
                'type' => $effective_type,
                'amount' => (float) $t->amount,
                'balance' => round($running_balance, 2),
            ];
        })->toArray();

        return [
            'opening_balance' => round($opening_balance, 2),
            'closing_balance' => round($running_balance, 2),
            'transactions' => $items,
        ];
    }

    /**
     * Compute budget variance analysis for a specific month and year.
     *
     * @return array<int, array<string, mixed>>
     */
    public function budget_goal_report(int $month, int $year): array
    {
        $goals = $this->budgetGoalRepository->for_month($month, $year);
        $spent_by_category = collect($this->transactionRepository->spent_by_category_map($month, $year));

        return $goals->map(function ($goal) use ($spent_by_category) {
            $spent = (float) ($spent_by_category->get($goal->category_id) ?? 0);
            $limit = (float) $goal->limit_amount;
            $variance = $limit - $spent;
            $percent = $limit > 0 ? round(($spent / $limit) * 100, 1) : 0;

            return [
                'category_name' => $goal->category?->name ?? 'Unknown',
                'category_icon' => $goal->category?->icon ?? 'tag',
                'limit_amount' => $limit,
                'actual_spent' => $spent,
                'variance' => $variance,
                'percent' => $percent,
                'status' => $percent < 75 ? 'safe' : ($percent < 90 ? 'warning' : 'danger'),
            ];
        })->toArray();
    }

    /**
     * Generate calendar-view daily aggregates for a month.
     *
     * @return array<string, array<string, mixed>>
     */
    public function calendar_report(int $month, int $year): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $day_transactions_grouped = $this->transactionRepository->calendar_transactions(
            $start->format('Y-m-d'),
            $end->format('Y-m-d')
        )->groupBy(fn ($t) => $t->transaction_date->format('Y-m-d'));

        $calendar = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $date = $cursor->format('Y-m-d');
            $day_transactions = $day_transactions_grouped->get($date, collect());

            $calendar[$date] = [
                'date' => $date,
                'day' => $cursor->day,
                'weekday' => $cursor->dayOfWeek,
                'is_today' => $cursor->isToday(),
                'income' => (float) $day_transactions->where('type', 'income')->sum('amount'),
                'expense' => (float) $day_transactions->where('type', 'expense')->sum('amount'),
                'transfer' => (float) $day_transactions->where('type', 'transfer')->sum('amount'),
                'items' => $day_transactions->map(fn ($t) => [
                    'id' => $t->id,
                    'description' => $t->description,
                    'amount' => (float) $t->amount,
                    'type' => $t->type->value ?? $t->type,
                    'category_name' => $t->category?->name,
                    'category_color' => $t->category?->color,
                    'account_name' => $t->account?->name,
                ]),
            ];
            $cursor->addDay();
        }

        return $calendar;
    }
}
