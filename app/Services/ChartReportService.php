<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\TransactionRepositoryInterface;
use Carbon\Carbon;

/**
 * Service class handling visual chart reporting datasets.
 */
class ChartReportService
{
    /**
     * Create a new ChartReportService instance.
     *
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository
    ) {}

    /**
     * Generate income vs expense aggregates grouped by month.
     *
     * @param string|null $from
     * @param string|null $to
     * @param int|null $person_id
     * @return array<int, array<string, mixed>>
     */
    public function income_vs_expense(?string $from = null, ?string $to = null, ?int $person_id = null): array
    {
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
            $key = sprintf('%04d-%02d', (int)$row->year, (int)$row->month);
            if (isset($months[$key])) {
                $type_str = $row->type instanceof \UnitEnum ? $row->type->value : $row->type;
                $months[$key][$type_str] = (float) $row->total;
            }
        }

        foreach ($months as &$m) {
            $m['net'] = $m['income'] - $m['expense'];
        }

        return array_values($months);
    }

    /**
     * Generate expense breakdown aggregates grouped by category.
     *
     * @param int $month
     * @param int $year
     * @param int|null $person_id
     * @return array<int, array<string, mixed>>
     */
    public function category_expense(int $month, int $year, ?int $person_id = null): array
    {
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
    }

    /**
     * Helper to get income vs expense for the last 6 months.
     *
     * @param int|null $person_id
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
     *
     * @param int $month
     * @param int $year
     * @param int|null $person_id
     * @return array<int, array<string, mixed>>
     */
    public function daily_spending_trend(int $month, int $year, ?int $person_id = null): array
    {
        $current_start = Carbon::create($year, $month, 1);
        $current_end = $current_start->copy()->endOfMonth();
        $today = now();
        if ($current_end->gt($today)) $current_end = $today;

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
    }

    /**
     * Compute the weekly spending trend over a rolling 12-week window.
     *
     * @param int|null $person_id
     * @return array<int, array<string, mixed>>
     */
    public function weekly_spending_trend(?int $person_id = null): array
    {
        $today = now();
        $current_start = now()->subWeeks(11)->startOfWeek();
        $current_end = now()->endOfWeek();
        if ($current_end->gt($today)) $current_end = $today;

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
            $labels[$i] = 'Wk ' . $cursor->format('M d');
            $cursor->addWeek();
        }

        foreach ($transactions as $t) {
            $date = Carbon::parse($t->transaction_date);

            if ($date->between($current_start, $current_end)) {
                $diff_in_weeks = $date->diffInWeeks($current_start);
                if (isset($current_buckets[$diff_in_weeks])) {
                    $current_buckets[$diff_in_weeks] += (float) $t->amount;
                }
            } elseif ($date->between($prev_start, $prev_end)) {
                $diff_in_weeks = $date->diffInWeeks($prev_start);
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
    }

    /**
     * Compute the yearly spending trend grouped by calendar months.
     *
     * @param int $year
     * @param int|null $person_id
     * @return array<int, array<string, mixed>>
     */
    public function yearly_spending_trend(int $year, ?int $person_id = null): array
    {
        $current_start = Carbon::create($year, 1, 1)->startOfYear();
        $current_end = $current_start->copy()->endOfYear();
        $today = now();
        if ($current_end->gt($today)) $current_end = $today;

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
    }
}
