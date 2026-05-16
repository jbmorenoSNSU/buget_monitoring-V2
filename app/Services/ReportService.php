<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Transaction;
use App\Models\BudgetGoal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function incomeVsExpense(?string $from = null, ?string $to = null): array
    {
        $from = $from ? Carbon::parse($from)->startOfMonth() : now()->subMonths(5)->startOfMonth();
        $to = $to ? Carbon::parse($to)->endOfMonth() : now()->endOfMonth();

        $data = Transaction::select(
                DB::raw('YEAR(transaction_date) as year'),
                DB::raw('MONTH(transaction_date) as month'),
                'type',
                DB::raw('SUM(amount) as total')
            )
            ->whereIn('type', ['income', 'expense'])
            ->whereBetween('transaction_date', [$from, $to])
            ->groupBy('year', 'month', 'type')
            ->orderBy('year')->orderBy('month')
            ->get();

        $months = [];
        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            $key = $cursor->format('Y-m');
            $months[$key] = [
                'label' => $cursor->format('M Y'),
                'month' => $cursor->month,
                'year' => $cursor->year,
                'income' => 0,
                'expense' => 0,
                'net' => 0,
            ];
            $cursor->addMonth();
        }

        foreach ($data as $row) {
            $key = sprintf('%04d-%02d', $row->year, $row->month);
            if (isset($months[$key])) {
                $typeStr = $row->type instanceof \UnitEnum ? $row->type->value : $row->type;
                $months[$key][$typeStr] = (float) $row->total;
            }
        }

        foreach ($months as &$m) {
            $m['net'] = $m['income'] - $m['expense'];
        }

        return array_values($months);
    }

    public function categoryExpense(int $month, int $year): array
    {
        $data = Transaction::select(
                'category_id',
                DB::raw('SUM(amount) as total')
            )
            ->with('category')
            ->where('type', 'expense')
            ->forMonth($month, $year)
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->get();

        $grandTotal = $data->sum('total');

        return $data->map(fn ($row) => [
            'category_id' => $row->category_id,
            'category_name' => $row->category?->name ?? 'Unknown',
            'category_icon' => $row->category?->icon ?? 'tag',
            'category_color' => $row->category?->color ?? '#94A3B8',
            'amount' => (float) $row->total,
            'percentage' => $grandTotal > 0 ? round(($row->total / $grandTotal) * 100, 1) : 0,
        ])->toArray();
    }

    public function accountStatement(int $accountId, string $from, string $to): array
    {
        $transactions = Transaction::with('category')
            ->where(function ($q) use ($accountId) {
                $q->where('account_id', $accountId)
                  ->orWhere('transfer_to_account_id', $accountId);
            })
            ->whereBetween('transaction_date', [$from, $to])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $openingBalance = $this->computeOpeningBalance($accountId, $from);

        $runningBalance = $openingBalance;
        $items = $transactions->map(function ($t) use (&$runningBalance, $accountId) {
            $type = $t->type->value ?? $t->type;
            if ($t->transfer_to_account_id == $accountId && $type === 'transfer') {
                $runningBalance += (float) $t->amount;
                $effectiveType = 'income';
            } elseif ($type === 'income') {
                $runningBalance += (float) $t->amount;
                $effectiveType = 'income';
            } else {
                $runningBalance -= (float) $t->amount;
                $effectiveType = 'expense';
            }

            return [
                'date' => $t->transaction_date->format('Y-m-d'),
                'description' => $t->description,
                'category' => $t->category?->name ?? 'Transfer',
                'type' => $effectiveType,
                'amount' => (float) $t->amount,
                'balance' => round($runningBalance, 2),
            ];
        })->toArray();

        return [
            'opening_balance' => round($openingBalance, 2),
            'closing_balance' => round($runningBalance, 2),
            'transactions' => $items,
        ];
    }

    private function computeOpeningBalance(int $accountId, string $before): float
    {
        $account = \App\Models\Account::find($accountId);
        $initial = (float) $account->initial_balance;

        $income = Transaction::where('account_id', $accountId)
            ->where('type', 'income')
            ->where('transaction_date', '<', $before)->sum('amount');
        $expense = Transaction::where('account_id', $accountId)
            ->where('type', 'expense')
            ->where('transaction_date', '<', $before)->sum('amount');
        $tOut = Transaction::where('account_id', $accountId)
            ->where('type', 'transfer')
            ->where('transaction_date', '<', $before)->sum('amount');
        $tIn = Transaction::where('transfer_to_account_id', $accountId)
            ->where('type', 'transfer')
            ->where('transaction_date', '<', $before)->sum('amount');

        return $initial + $income - $expense - $tOut + $tIn;
    }

    public function budgetGoalReport(int $month, int $year): array
    {
        $goals = BudgetGoal::with('category')->forMonth($month, $year)->get();

        return $goals->map(function ($goal) use ($month, $year) {
            $spent = (float) Transaction::where('category_id', $goal->category_id)
                ->where('type', 'expense')->forMonth($month, $year)->sum('amount');
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

    public function last6MonthsChart(): array
    {
        return $this->incomeVsExpense(
            now()->subMonths(5)->startOfMonth()->format('Y-m-d'),
            now()->endOfMonth()->format('Y-m-d')
        );
    }

    public function dailySpendingTrend(int $month, int $year): array
    {
        $start = Carbon::create($year, $month, 1);
        $end = $start->copy()->endOfMonth();
        $today = now();
        if ($end->gt($today)) $end = $today;

        $data = Transaction::select(
                DB::raw('DAY(transaction_date) as day'),
                DB::raw('SUM(amount) as total')
            )
            ->where('type', 'expense')
            ->forMonth($month, $year)
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        $result = [];
        for ($d = 1; $d <= $end->day; $d++) {
            $result[] = [
                'day' => $d,
                'amount' => (float) ($data[$d] ?? 0),
            ];
        }

        return $result;
    }

    public function calendarReport(int $month, int $year): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        
        $transactions = Transaction::with(['category', 'account'])
            ->whereBetween('transaction_date', [$start, $end])
            ->orderBy('transaction_date')
            ->get()
            ->groupBy(fn($t) => $t->transaction_date->format('Y-m-d'));

        $calendar = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $date = $cursor->format('Y-m-d');
            $dayTransactions = $transactions->get($date, collect());
            
            $calendar[$date] = [
                'date' => $date,
                'day' => $cursor->day,
                'weekday' => $cursor->dayOfWeek,
                'is_today' => $cursor->isToday(),
                'income' => (float) $dayTransactions->where('type', 'income')->sum('amount'),
                'expense' => (float) $dayTransactions->where('type', 'expense')->sum('amount'),
                'transfer' => (float) $dayTransactions->where('type', 'transfer')->sum('amount'),
                'items' => $dayTransactions->map(fn($t) => [
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
