<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\AccountRepositoryInterface;
use App\Interfaces\DebtRepositoryInterface;
use App\Interfaces\RecurringTransactionRepositoryInterface;
use App\Interfaces\SavingsGoalRepositoryInterface;
use App\Interfaces\TransactionRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Service class handling visual chart reporting datasets.
 */
class ChartReportService
{
    /**
     * Create a new ChartReportService instance.
     */
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository,
        private AccountRepositoryInterface $accountRepository,
        private RecurringTransactionRepositoryInterface $recurringRepository,
        private DebtRepositoryInterface $debtRepository,
        private SavingsGoalRepositoryInterface $savingsGoalRepository
    ) {}

    /**
     * Generate income vs expense aggregates grouped by month.
     * This method looks back over the past few months (usually 6), adds up all the
     * money that came in (income) and went out (expense), and subtracts them to
     * figure out your "Net" money for each month.
     *
     * @return array<int, array<string, mixed>>
     */
    public function income_vs_expense(?string $from = null, ?string $to = null, ?int $person_id = null): array
    {
        $version = Cache::get('reports_cache_version', 1);
        $key = "reports:income_vs_expense:{$from}:{$to}:{$person_id}:v{$version}";

        return Cache::remember($key, 3600, function () use ($from, $to, $person_id) {
            $fromDate = $from ? Carbon::parse($from)->startOfMonth() : now()->subMonths(5)->startOfMonth();
            $toDate = $to ? Carbon::parse($to)->endOfMonth() : now()->endOfMonth();

            $data = $this->transactionRepository->income_vs_expense_raw(
                $fromDate->format('Y-m-d'),
                $toDate->format('Y-m-d'),
                $person_id
            );

            $months = [];
            $cursor = $fromDate->copy();
            while ($cursor->lte($toDate)) {
                $mkey = $cursor->format('Y-m');
                $months[$mkey] = [
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
                $mkey = sprintf('%04d-%02d', (int) $row->year, (int) $row->month);
                if (isset($months[$mkey])) {
                    $type_str = $row->type instanceof \UnitEnum ? $row->type->value : $row->type;
                    $months[$mkey][$type_str] = (float) $row->total;
                }
            }

            foreach ($months as &$m) {
                $m['net'] = $m['income'] - $m['expense'];
            }

            return array_values($months);
        });
    }

    /**
     * Generate expense breakdown aggregates grouped by category.
     *
     * @return array<int, array<string, mixed>>
     */
    public function category_expense(int $month, int $year, ?int $person_id = null): array
    {
        $version = Cache::get('reports_cache_version', 1);
        $key = "reports:category_expense:{$month}:{$year}:{$person_id}:v{$version}";

        return Cache::remember($key, 3600, function () use ($month, $year, $person_id) {
            $data = $this->transactionRepository->category_expense_raw($month, $year, $person_id);
            $grand_total = $data->sum('total');

            return $data->map(fn ($row) => [
                'category_id' => $row->category_id,
                'category_name' => $row->category?->name ?? 'Unknown',
                'category_icon' => $row->category?->icon ?? 'tag',
                'category_color' => $row->category?->color ?? '#94A3B8',
                'amount' => (float) $row->total,
                'percentage' => $grand_total > 0 ? round(($row->total / $grand_total) * 100, 1) : 0,
            ])->toArray();
        });
    }

    /**
     * Helper to get income vs expense for the last 6 months.
     *
     * @return array<int, array<string, mixed>>
     */
    public function last_6_months_chart(?int $person_id = null): array
    {
        return $this->income_vs_expense(
            now()->subMonths(5)->startOfMonth()->format('Y-m-d'),
            now()->endOfMonth()->format('Y-m-d'),
            $person_id
        );
    }

    /**
     * Compute the daily spending trend for a specific month and year.
     * This compares how much you spent on Day 1, Day 2, Day 3, etc. of the current month
     * versus exactly how much you spent on those exact same days last month.
     * It helps you see if you are spending faster or slower than before.
     *
     * @return array<int, array<string, mixed>>
     */
    public function daily_spending_trend(int $month, int $year, ?int $person_id = null): array
    {
        $version = Cache::get('reports_cache_version', 1);
        $key = "reports:daily_spending_trend:{$month}:{$year}:{$person_id}:v{$version}";

        return Cache::remember($key, 3600, function () use ($month, $year, $person_id) {
            $current_start = Carbon::create($year, $month, 1);
            $current_end = $current_start->copy()->endOfMonth();
            $today = now();
            if ($current_end->gt($today)) {
                $current_end = $today;
            }

            $prev_start = $current_start->copy()->subMonth()->startOfMonth();
            $prev_end = $current_start->copy()->subMonth()->endOfMonth();

            $transactions = $this->transactionRepository->expense_by_date_range(
                $prev_start->format('Y-m-d'),
                $current_end->format('Y-m-d'),
                $person_id
            );

            $current_data = [];
            $prev_data = [];
            foreach ($transactions as $t) {
                $date = Carbon::parse($t->transaction_date);
                if ($date->between($current_start, $current_end)) {
                    $current_data[$date->day] = ($current_data[$date->day] ?? 0) + (float) $t->amount;
                } elseif ($date->between($prev_start, $prev_end)) {
                    $prev_data[$date->day] = ($prev_data[$date->day] ?? 0) + (float) $t->amount;
                }
            }

            $result = [];
            $run_current = 0;
            $run_prev = 0;
            $max_days = $current_start->daysInMonth;

            for ($d = 1; $d <= $max_days; $d++) {
                $run_current += $current_data[$d] ?? 0;
                $run_prev += $prev_data[$d] ?? 0;

                $is_future = $d > $current_end->day && $current_start->format('Y-m') === $today->format('Y-m');

                $result[] = [
                    'day' => $d,
                    'label' => "Day $d",
                    'current_amount' => $is_future ? null : round($run_current, 2),
                    'previous_amount' => round($run_prev, 2),
                ];
            }

            return $result;
        });
    }

    /**
     * Compute the weekly spending trend over a rolling 12-week window.
     *
     * @return array<int, array<string, mixed>>
     */
    public function weekly_spending_trend(?int $person_id = null): array
    {
        $version = Cache::get('reports_cache_version', 1);
        $key = "reports:weekly_spending_trend:{$person_id}:v{$version}";

        return Cache::remember($key, 3600, function () use ($person_id) {
            $today = now();
            $current_start = now()->subWeeks(11)->startOfWeek();
            $current_end = now()->endOfWeek();
            if ($current_end->gt($today)) {
                $current_end = $today;
            }

            $prev_start = $current_start->copy()->subWeeks(12)->startOfWeek();
            $prev_end = $current_start->copy()->subWeeks(1)->endOfWeek();

            $transactions = $this->transactionRepository->expense_by_date_range(
                $prev_start->format('Y-m-d'),
                $current_end->format('Y-m-d'),
                $person_id
            );

            $current_buckets = array_fill(0, 12, 0);
            $prev_buckets = array_fill(0, 12, 0);
            $labels = [];

            $cursor = $current_start->copy();
            for ($i = 0; $i < 12; $i++) {
                $labels[$i] = 'Wk '.$cursor->format('M d');
                $cursor->addWeek();
            }

            foreach ($transactions as $t) {
                $date = Carbon::parse($t->transaction_date);

                if ($date->between($current_start, $current_end)) {
                    $diff_in_weeks = (int) floor($current_start->diffInWeeks($date));
                    if (isset($current_buckets[$diff_in_weeks])) {
                        $current_buckets[$diff_in_weeks] += (float) $t->amount;
                    }
                } elseif ($date->between($prev_start, $prev_end)) {
                    $diff_in_weeks = (int) floor($prev_start->diffInWeeks($date));
                    if (isset($prev_buckets[$diff_in_weeks])) {
                        $prev_buckets[$diff_in_weeks] += (float) $t->amount;
                    }
                }
            }

            $result = [];
            $run_current = 0;
            $run_prev = 0;

            for ($i = 0; $i < 12; $i++) {
                $run_current += $current_buckets[$i];
                $run_prev += $prev_buckets[$i];

                $week_start = $current_start->copy()->addWeeks($i);
                $is_future = $week_start->gt($today);

                $result[] = [
                    'label' => $labels[$i],
                    'current_amount' => $is_future ? null : round($run_current, 2),
                    'previous_amount' => round($run_prev, 2),
                ];
            }

            return $result;
        });
    }

    /**
     * Compute the yearly spending trend grouped by calendar months.
     *
     * @return array<int, array<string, mixed>>
     */
    public function yearly_spending_trend(int $year, ?int $person_id = null): array
    {
        $version = Cache::get('reports_cache_version', 1);
        $key = "reports:yearly_spending_trend:{$year}:{$person_id}:v{$version}";

        return Cache::remember($key, 3600, function () use ($year, $person_id) {
            $current_start = Carbon::create($year, 1, 1)->startOfYear();
            $current_end = $current_start->copy()->endOfYear();
            $today = now();
            if ($current_end->gt($today)) {
                $current_end = $today;
            }

            $prev_start = $current_start->copy()->subYear()->startOfYear();
            $prev_end = $current_start->copy()->subYear()->endOfYear();

            $transactions = $this->transactionRepository->expense_by_date_range(
                $prev_start->format('Y-m-d'),
                $current_end->format('Y-m-d'),
                $person_id
            );

            $current_data = [];
            $prev_data = [];
            foreach ($transactions as $t) {
                $date = Carbon::parse($t->transaction_date);
                if ($date->between($current_start, $current_end)) {
                    $current_data[$date->month] = ($current_data[$date->month] ?? 0) + (float) $t->amount;
                } elseif ($date->between($prev_start, $prev_end)) {
                    $prev_data[$date->month] = ($prev_data[$date->month] ?? 0) + (float) $t->amount;
                }
            }

            $result = [];
            $run_current = 0;
            $run_prev = 0;

            for ($m = 1; $m <= 12; $m++) {
                $run_current += $current_data[$m] ?? 0;
                $run_prev += $prev_data[$m] ?? 0;

                $is_future = $m > $current_end->month && $current_start->year === $today->year;

                $result[] = [
                    'label' => Carbon::create($year, $m, 1)->format('M'),
                    'current_amount' => $is_future ? null : round($run_current, 2),
                    'previous_amount' => round($run_prev, 2),
                ];
            }

            return $result;
        });
    }

    /**
     * Compute a Year-in-Review summary for a given year.
     * This generates a "Spotify Wrapped" style summary of your finances for the year,
     * finding your total savings, your top 5 most expensive categories, and the single 
     * month where you spent the most money.
     *
     * @return array<string, mixed>
     */
    public function year_in_review(int $year): array
    {
        $version = Cache::get('reports_cache_version', 1);
        $key = "reports:year_in_review:{$year}:v{$version}";

        return Cache::remember($key, 3600, function () use ($year) {
            $start = Carbon::create($year, 1, 1)->startOfYear();
            $end = $start->copy()->endOfYear();

            $today = now();
            if ($end->gt($today)) {
                $end = $today;
            }

            $totals = $this->transactionRepository->year_in_review_totals(
                $start->format('Y-m-d'),
                $end->format('Y-m-d')
            );

            $total_income = $totals['income'];
            $total_expense = $totals['expense'];
            $net_savings = $total_income - $total_expense;

            $categories = $this->transactionRepository->year_in_review_top_categories(
                $start->format('Y-m-d'),
                $end->format('Y-m-d'),
                5
            );

            $busiest = $this->transactionRepository->year_in_review_busiest_month(
                $start->format('Y-m-d'),
                $end->format('Y-m-d')
            );

            $busiest_month_name = $busiest['month'] ? Carbon::create($year, $busiest['month'], 1)->format('F') : 'N/A';
            $busiest_month_amount = $busiest['amount'];

            return [
                'year' => $year,
                'total_income' => round($total_income, 2),
                'total_expense' => round($total_expense, 2),
                'net_savings' => round($net_savings, 2),
                'top_categories' => $categories,
                'busiest_month' => [
                    'name' => $busiest_month_name,
                    'amount' => round($busiest_month_amount, 2),
                ],
            ];
        });
    }

    /**
     * Generate 180-day daily cashflow balance projections.
     * This is the most complex financial calculation in the app. It figures out 
     * exactly how much money you will have every day for the next 6 months by:
     * 1. Looking at your past 90 days to figure out your average daily "random" spending.
     * 2. Looking at all your upcoming Bills, Subscriptions, and Debt Payments.
     * 3. Looking at your scheduled Savings Goal contributions.
     * 4. Simulating your bank balance day-by-day combining all of the above.
     *
     * @return array<string, mixed>
     */
    public function cashflow_projection(): array
    {
        $version = Cache::get('reports_cache_version', 1);
        $key = "reports:cashflow_projection:v{$version}";

        return Cache::remember($key, 3600, function () {
            // 1. Calculate Average Daily Growth via repository — no direct model queries in service
            $threeMonthsAgo = now()->subDays(90)->toDateString();
            $today = now()->toDateString();

            $net = $this->transactionRepository->non_recurring_net_for_projection($threeMonthsAgo, $today);
            $netDailyGrowth = ($net['income'] - $net['expense']) / 90.0;

            // 2. Fetch Active Recurring Transactions via upcoming(180) — filters is_active at DB level
            //    Do NOT use all()->where() — that fetches all records into memory before filtering.
            $recurrings = $this->recurringRepository->upcoming(180);
            $startProj = now()->startOfDay();
            $endProj = now()->addDays(180)->endOfDay();

            $recurringEvents = [];
            foreach ($recurrings as $rec) {
                if (! $rec->next_due_date) {
                    continue;
                }
                $next = Carbon::parse($rec->next_due_date)->startOfDay();
                $end = $rec->end_date ? Carbon::parse($rec->end_date)->startOfDay() : null;
                $freq = $rec->frequency->value ?? $rec->frequency;
                $amount = (float) $rec->amount;
                $type = $rec->type->value ?? $rec->type;
                $desc = $rec->description;

                while ($next->lte($endProj)) {
                    if ($next->gte($startProj) && (! $end || $next->lte($end))) {
                        $dateStr = $next->format('Y-m-d');
                        if (! isset($recurringEvents[$dateStr])) {
                            $recurringEvents[$dateStr] = [];
                        }
                        $recurringEvents[$dateStr][] = [
                            'amount' => $amount,
                            'type' => $type,
                            'description' => $desc,
                        ];
                    }

                    switch ($freq) {
                        case 'daily':
                            $next->addDay();
                            break;
                        case 'weekly':
                            $next->addWeek();
                            break;
                        case 'monthly':
                            $next->addMonth();
                            break;
                        case 'yearly':
                            $next->addYear();
                            break;
                        default:
                            break 2;
                    }
                }
            }

            // 2.1 Integrate Active Debt Payments
            $debts = $this->debtRepository->get_active();
            foreach ($debts as $debt) {
                if (! $debt->due_date_day || ! $debt->minimum_payment) {
                    continue;
                }

                for ($i = 0; $i < 7; $i++) {
                    $dueDate = now()->startOfMonth()->addMonths($i);
                    $day = min((int) $debt->due_date_day, $dueDate->daysInMonth);
                    $dueDate->day($day)->startOfDay();

                    if ($dueDate->between($startProj, $endProj)) {
                        $dateStr = $dueDate->format('Y-m-d');
                        if (! isset($recurringEvents[$dateStr])) {
                            $recurringEvents[$dateStr] = [];
                        }
                        $recurringEvents[$dateStr][] = [
                            'amount' => (float) $debt->minimum_payment,
                            'type' => 'expense',
                            'description' => "Debt Payment: {$debt->name}",
                        ];
                    }
                }
            }

            // 2.2 Integrate Scheduled Savings Contributions
            $savingsGoals = $this->savingsGoalRepository->all();
            foreach ($savingsGoals as $goal) {
                if ($goal->current_amount >= $goal->target_amount || ! $goal->target_date) {
                    continue;
                }

                $targetDate = Carbon::parse($goal->target_date)->startOfDay();
                if ($targetDate->isPast()) {
                    continue;
                }

                $remaining = (float) $goal->target_amount - (float) $goal->current_amount;
                $monthsRemaining = (int) ceil(now()->startOfDay()->floatDiffInMonths($targetDate));
                if ($monthsRemaining <= 0) {
                    $monthsRemaining = 1;
                }

                $monthlyContribution = $remaining / $monthsRemaining;

                for ($i = 0; $i < 7; $i++) {
                    $contribDate = now()->startOfMonth()->addMonths($i);
                    $day = min($targetDate->day, $contribDate->daysInMonth);
                    $contribDate->day($day)->startOfDay();

                    if ($contribDate->between($startProj, min($endProj, $targetDate))) {
                        $dateStr = $contribDate->format('Y-m-d');
                        if (! isset($recurringEvents[$dateStr])) {
                            $recurringEvents[$dateStr] = [];
                        }
                        $recurringEvents[$dateStr][] = [
                            'amount' => round($monthlyContribution, 2),
                            'type' => 'expense',
                            'description' => "Savings: {$goal->name}",
                        ];
                    }
                }
            }

            // 3. Simulate Projections Day-by-Day
            $startingCash = (float) $this->accountRepository->all_active()->sum('current_balance');
            $currentBalance = $startingCash;
            $dailyPoints = [];
            $milestones = [];

            $cursor = now()->startOfDay();

            // Day 0
            $dailyPoints[] = [
                'date' => $cursor->format('Y-m-d'),
                'balance' => round($currentBalance, 2),
            ];

            for ($day = 1; $day <= 180; $day++) {
                $cursor->addDay();
                $dateStr = $cursor->format('Y-m-d');

                // Apply daily growth
                $currentBalance += $netDailyGrowth;

                // Apply recurring events
                if (isset($recurringEvents[$dateStr])) {
                    foreach ($recurringEvents[$dateStr] as $event) {
                        if ($event['type'] === 'income') {
                            $currentBalance += $event['amount'];
                        } else {
                            $currentBalance -= $event['amount'];
                        }

                        $milestones[] = [
                            'date' => $dateStr,
                            'description' => $event['description'],
                            'amount' => $event['amount'],
                            'type' => $event['type'],
                            'projected_balance' => round($currentBalance, 2),
                        ];
                    }
                }

                $dailyPoints[] = [
                    'date' => $dateStr,
                    'balance' => round($currentBalance, 2),
                ];
            }

            // 4. Summarize and Format Results
            $balances = collect($dailyPoints)->pluck('balance');
            $projectedHigh = (float) $balances->max();
            $projectedLow = (float) $balances->min();
            $endingBalance = (float) $balances->last();
            $netChange = $endingBalance - $startingCash;

            return [
                'starting_balance' => round($startingCash, 2),
                'ending_balance' => round($endingBalance, 2),
                'projected_high' => round($projectedHigh, 2),
                'projected_low' => round($projectedLow, 2),
                'net_change' => round($netChange, 2),
                'daily_growth_rate' => round($netDailyGrowth, 2),
                'daily_points' => $dailyPoints,
                'milestones' => collect($milestones)->sortBy('date')->take(20)->values()->toArray(),
            ];
        });
    }
}
