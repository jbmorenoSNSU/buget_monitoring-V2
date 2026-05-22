<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Transaction;
use App\Models\BudgetGoal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function incomeVsExpense(?string $from = null, ?string $to = null, ?int $personId = null): array
    {
        $from = $from ? Carbon::parse($from)->startOfMonth() : now()->subMonths(5)->startOfMonth();
        $to = $to ? Carbon::parse($to)->endOfMonth() : now()->endOfMonth();

        $query = Transaction::select(
                DB::raw('YEAR(transaction_date) as year'),
                DB::raw('MONTH(transaction_date) as month'),
                'type',
                DB::raw('SUM(amount) as total')
            )
            ->whereIn('type', ['income', 'expense'])
            ->whereBetween('transaction_date', [$from, $to]);

        if ($personId) {
            $query->whereHas('account', fn ($q) => $q->where('person_id', $personId));
        }

        $data = $query->groupBy('year', 'month', 'type')
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

    public function categoryExpense(int $month, int $year, ?int $personId = null): array
    {
        $query = Transaction::select('category_id', DB::raw('SUM(amount) as total'))
            ->where('type', 'expense')
            ->forMonth($month, $year);

        if ($personId) {
            $query->whereHas('account', fn ($q) => $q->where('person_id', $personId));
        }

        $data = $query->with('category:id,name,color,icon')
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
        $account = \App\Models\Account::select('initial_balance')->find($accountId);
        $initial = (float) $account->initial_balance;

        $totals = Transaction::where('transaction_date', '<', $before)
            ->selectRaw('
                COALESCE(SUM(CASE WHEN type = "income" AND account_id = ? THEN amount ELSE 0 END), 0) as income,
                COALESCE(SUM(CASE WHEN type = "expense" AND account_id = ? THEN amount ELSE 0 END), 0) as expense,
                COALESCE(SUM(CASE WHEN type = "transfer" AND account_id = ? THEN amount ELSE 0 END), 0) as transfer_out,
                COALESCE(SUM(CASE WHEN type = "transfer" AND transfer_to_account_id = ? THEN amount ELSE 0 END), 0) as transfer_in
            ', [$accountId, $accountId, $accountId, $accountId])
            ->first();

        return $initial + (float)$totals->income - (float)$totals->expense - (float)$totals->transfer_out + (float)$totals->transfer_in;
    }

    public function budgetGoalReport(int $month, int $year): array
    {
        $goals = BudgetGoal::with('category:id,name,icon')->forMonth($month, $year)->get();

        $spentByCategory = Transaction::where('type', 'expense')
            ->forMonth($month, $year)
            ->selectRaw('category_id, SUM(amount) as spent')
            ->groupBy('category_id')
            ->pluck('spent', 'category_id');

        return $goals->map(function ($goal) use ($spentByCategory) {
            $spent = (float) ($spentByCategory->get($goal->category_id) ?? 0);
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

    public function last6MonthsChart(?int $personId = null): array
    {
        return $this->incomeVsExpense(
            now()->subMonths(5)->startOfMonth()->format('Y-m-d'),
            now()->endOfMonth()->format('Y-m-d'),
            $personId
        );
    }

    public function dailySpendingTrend(int $month, int $year, ?int $personId = null): array
    {
        $currentStart = Carbon::create($year, $month, 1);
        $currentEnd = $currentStart->copy()->endOfMonth();
        $today = now();
        if ($currentEnd->gt($today)) $currentEnd = $today;

        $prevStart = $currentStart->copy()->subMonth()->startOfMonth();
        $prevEnd = $currentStart->copy()->subMonth()->endOfMonth();

        $query = Transaction::select('transaction_date', 'amount')
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$prevStart, $currentEnd]);

        if ($personId) {
            $query->whereHas('account', fn ($q) => $q->where('person_id', $personId));
        }

        $transactions = $query->get();

        $currentData = [];
        $prevData = [];
        foreach ($transactions as $t) {
            $date = Carbon::parse($t->transaction_date);
            if ($date->between($currentStart, $currentEnd)) {
                $currentData[$date->day] = ($currentData[$date->day] ?? 0) + (float) $t->amount;
            } elseif ($date->between($prevStart, $prevEnd)) {
                $prevData[$date->day] = ($prevData[$date->day] ?? 0) + (float) $t->amount;
            }
        }

        $result = [];
        $runCurrent = 0;
        $runPrev = 0;
        $maxDays = $currentStart->daysInMonth;
        
        for ($d = 1; $d <= $maxDays; $d++) {
            $runCurrent += $currentData[$d] ?? 0;
            $runPrev += $prevData[$d] ?? 0;
            
            $isFuture = $d > $currentEnd->day && $currentStart->format('Y-m') === $today->format('Y-m');
            
            $result[] = [
                'day' => $d,
                'label' => "Day $d",
                'current_amount' => $isFuture ? null : round($runCurrent, 2),
                'previous_amount' => round($runPrev, 2),
            ];
        }

        return $result;
    }

    public function weeklySpendingTrend(?int $personId = null): array
    {
        $today = now();
        $currentStart = now()->subWeeks(11)->startOfWeek(); // 12 weeks total
        $currentEnd = now()->endOfWeek();
        if ($currentEnd->gt($today)) $currentEnd = $today;

        $prevStart = $currentStart->copy()->subWeeks(12)->startOfWeek();
        $prevEnd = $currentStart->copy()->subWeeks(1)->endOfWeek();

        $query = Transaction::select('transaction_date', 'amount')
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$prevStart, $currentEnd]);

        if ($personId) {
            $query->whereHas('account', fn ($q) => $q->where('person_id', $personId));
        }

        $transactions = $query->get();

        $currentBuckets = array_fill(0, 12, 0);
        $prevBuckets = array_fill(0, 12, 0);
        $labels = [];

        $cursor = $currentStart->copy();
        for ($i = 0; $i < 12; $i++) {
            $labels[$i] = 'Wk ' . $cursor->format('M d');
            $cursor->addWeek();
        }

        foreach ($transactions as $t) {
            $date = Carbon::parse($t->transaction_date);
            
            if ($date->between($currentStart, $currentEnd)) {
                $diffInWeeks = $date->diffInWeeks($currentStart);
                if (isset($currentBuckets[$diffInWeeks])) {
                    $currentBuckets[$diffInWeeks] += (float) $t->amount;
                }
            } elseif ($date->between($prevStart, $prevEnd)) {
                $diffInWeeks = $date->diffInWeeks($prevStart);
                if (isset($prevBuckets[$diffInWeeks])) {
                    $prevBuckets[$diffInWeeks] += (float) $t->amount;
                }
            }
        }

        $result = [];
        $runCurrent = 0;
        $runPrev = 0;

        for ($i = 0; $i < 12; $i++) {
            $runCurrent += $currentBuckets[$i];
            $runPrev += $prevBuckets[$i];
            
            $weekStart = $currentStart->copy()->addWeeks($i);
            $isFuture = $weekStart->gt($today);
            
            $result[] = [
                'label' => $labels[$i],
                'current_amount' => $isFuture ? null : round($runCurrent, 2),
                'previous_amount' => round($runPrev, 2),
            ];
        }

        return $result;
    }

    public function yearlySpendingTrend(int $year, ?int $personId = null): array
    {
        $currentStart = Carbon::create($year, 1, 1)->startOfYear();
        $currentEnd = $currentStart->copy()->endOfYear();
        $today = now();
        if ($currentEnd->gt($today)) $currentEnd = $today;

        $prevStart = $currentStart->copy()->subYear()->startOfYear();
        $prevEnd = $currentStart->copy()->subYear()->endOfYear();

        $query = Transaction::select('transaction_date', 'amount')
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$prevStart, $currentEnd]);

        if ($personId) {
            $query->whereHas('account', fn ($q) => $q->where('person_id', $personId));
        }

        $transactions = $query->get();

        $currentData = [];
        $prevData = [];
        foreach ($transactions as $t) {
            $date = Carbon::parse($t->transaction_date);
            if ($date->between($currentStart, $currentEnd)) {
                $currentData[$date->month] = ($currentData[$date->month] ?? 0) + (float) $t->amount;
            } elseif ($date->between($prevStart, $prevEnd)) {
                $prevData[$date->month] = ($prevData[$date->month] ?? 0) + (float) $t->amount;
            }
        }

        $result = [];
        $runCurrent = 0;
        $runPrev = 0;

        for ($m = 1; $m <= 12; $m++) {
            $runCurrent += $currentData[$m] ?? 0;
            $runPrev += $prevData[$m] ?? 0;
            
            $isFuture = $m > $currentEnd->month && $currentStart->year === $today->year;

            $result[] = [
                'label' => Carbon::create($year, $m, 1)->format('M'),
                'current_amount' => $isFuture ? null : round($runCurrent, 2),
                'previous_amount' => round($runPrev, 2),
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
