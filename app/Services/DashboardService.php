<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\AccountRepositoryInterface;
use App\Interfaces\DebtRepositoryInterface;
use App\Interfaces\RecurringTransactionRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Service class handling dashboard statistics, health scoring, and cash flow forecasting.
 */
class DashboardService
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private TransactionService $transactionService,
        private BudgetGoalService $budgetGoalService,
        private RecurringTransactionService $recurringService,
        private DebtRepositoryInterface $debtRepository,
        private RecurringTransactionRepositoryInterface $recurringRepository
    ) {}

    /**
     * Get aggregated stats for the dashboard (cached per month/year/person).
     *
     * @return array<string, mixed>
     */
    public function get_dashboard_stats(int $month, int $year, ?int $person_id = null): array
    {
        $key = 'dashboard:stats:'.$month.':'.$year.':'.($person_id ?? 'all');

        return Cache::remember($key, now()->addDay(), function () use ($month, $year, $person_id) {
            $totalBalance = $this->accountRepository->total_balance($person_id);
            $budgetGoals  = $this->budgetGoalService->get_for_month($month, $year, $person_id);

            $remainingBudgets       = $this->calculateRemainingBudgets($budgetGoals, $person_id);
            $filteredUpcomingRecurring = $this->get_upcoming_recurring($person_id);
            $upcomingExpenses       = $this->calculateUpcomingExpenses($filteredUpcomingRecurring);

            $safeToSpend      = max(0, $totalBalance - $remainingBudgets - $upcomingExpenses);
            $safeToSpendDaily = $this->calculateDailySafeToSpend($safeToSpend, $month, $year);

            $monthlyIncome  = $this->transactionService->get_monthly_income($month, $year, $person_id);
            $monthlyExpense = $this->transactionService->get_monthly_expense($month, $year, $person_id);

            $healthScoreAndBadges = $this->calculateHealthScore($monthlyIncome, $monthlyExpense, $safeToSpend, $budgetGoals, $person_id);

            return [
                'totalBalance'     => $totalBalance,
                'safeToSpend'      => $safeToSpend,
                'safeToSpendDaily' => $safeToSpendDaily,
                'healthScore'      => $healthScoreAndBadges['score'],
                'badges'           => $healthScoreAndBadges['badges'],
                'monthlyIncome'    => $monthlyIncome,
                'monthlyExpense'   => $monthlyExpense,
            ];
        });
    }

    /**
     * Get upcoming recurring transactions optionally filtered by person.
     */
    public function get_upcoming_recurring(?int $person_id = null): Collection
    {
        $upcomingRecurring = $this->recurringService->get_upcoming(30);

        return $upcomingRecurring->filter(function ($r) use ($person_id) {
            if ($person_id && $r->account && $r->account->person_id !== $person_id) {
                return false;
            }

            return true;
        })->values();
    }

    /**
     * Generate a 30-day cash flow forecast based on active recurring transactions.
     *
     * @return array<int, array<string, mixed>>
     */
    public function generate_cash_flow_forecast(float $initialBalance, ?int $person_id = null): array
    {
        $forecast         = [];
        $projectedBalance = $initialBalance;
        $today            = now()->startOfDay();
        $dailyHits        = array_fill(0, 31, 0);
        $allRecurring     = $this->recurringRepository->all();

        foreach ($allRecurring as $rec) {
            if (! $rec->is_active) {
                continue;
            }
            if ($person_id && $rec->account && $rec->account->person_id !== $person_id) {
                continue;
            }

            $nextDue = Carbon::parse($rec->next_due_date)->startOfDay();
            $endDate = $rec->end_date ? Carbon::parse($rec->end_date)->startOfDay() : null;
            $amount  = (float) $rec->amount;
            $type    = $rec->type->value ?? $rec->type;
            $amount  = $type === 'income' ? $amount : -$amount;

            $hitDate = $nextDue->copy();
            $freq    = $rec->frequency->value ?? $rec->frequency;

            while ($hitDate->diffInDays($today, false) >= -30) {
                $daysFromNow = max(0, (int) $today->diffInDays($hitDate, false));

                if ($daysFromNow <= 30 && (! $endDate || $hitDate->lte($endDate))) {
                    $dailyHits[$daysFromNow] += $amount;
                }

                match ($freq) {
                    'daily'   => $hitDate->addDay(),
                    'weekly'  => $hitDate->addWeek(),
                    'monthly' => $hitDate->addMonth(),
                    'yearly'  => $hitDate->addYear(),
                };
            }
        }

        for ($i = 0; $i <= 30; $i++) {
            $projectedBalance += $dailyHits[$i];
            $forecast[] = [
                'date'    => now()->addDays($i)->format('M d'),
                'balance' => $projectedBalance,
            ];
        }

        return $forecast;
    }

    private function calculateRemainingBudgets(Collection $budgetGoals, ?int $person_id): float
    {
        if ($person_id === null) {
            return $budgetGoals->groupBy('category_id')->sum(function ($goals) {
                return $goals->max(fn ($g) => max(0, $g->remaining));
            });
        }

        return $budgetGoals->sum(fn ($g) => max(0, $g->remaining));
    }

    private function calculateUpcomingExpenses(Collection $upcomingRecurring): float
    {
        return $upcomingRecurring->filter(function ($r) {
            $type = $r->type->value ?? $r->type;

            return $type === 'expense';
        })->sum('amount');
    }

    private function calculateDailySafeToSpend(float $safeToSpend, int $month, int $year): float
    {
        $now = now();
        if ($now->month !== $month || $now->year !== $year) {
            $now           = Carbon::createFromDate($year, $month, 1);
            $daysRemaining = $now->daysInMonth;
        } else {
            $daysRemaining = max(1, $now->daysInMonth - $now->day + 1);
        }

        return $safeToSpend / $daysRemaining;
    }

    private function calculateHealthScore(float $monthlyIncome, float $monthlyExpense, float $safeToSpend, Collection $budgetGoals, ?int $person_id): array
    {
        $healthScore = 0;
        $badges      = [];

        if ($monthlyIncome > 0) {
            $savingsRate = ($monthlyIncome - $monthlyExpense) / $monthlyIncome;
            if ($savingsRate >= 0.20) {
                $healthScore += 40;
                $badges[] = ['id' => 'super_saver', 'name' => 'Super Saver', 'color' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30', 'icon' => 'PiggyBank'];
            } elseif ($savingsRate > 0) {
                $healthScore += 30;
                $badges[] = ['id' => 'saver', 'name' => 'Saver', 'color' => 'bg-teal-500/20 text-teal-400 border-teal-500/30', 'icon' => 'TrendingUp'];
            }
        } elseif ($monthlyExpense == 0) {
            $healthScore += 20;
        }

        if ($safeToSpend > 0) {
            $healthScore += 30;
            $badges[] = ['id' => 'buffer', 'name' => 'Healthy Buffer', 'color' => 'bg-blue-500/20 text-blue-400 border-blue-500/30', 'icon' => 'ShieldCheck'];
        }

        if ($budgetGoals->isNotEmpty()) {
            $breachedGoals = $budgetGoals->filter(fn ($g) => $g->percent >= 100);
            if ($breachedGoals->isEmpty()) {
                $healthScore += 30;
                $badges[] = ['id' => 'under_budget', 'name' => 'Under Budget', 'color' => 'bg-indigo-500/20 text-indigo-400 border-indigo-500/30', 'icon' => 'Target'];
            } elseif ($breachedGoals->count() <= 2) {
                $healthScore += 15;
            }
        } else {
            $healthScore += 15;
        }

        if ($this->debtRepository->count_active($person_id) === 0) {
            $healthScore += 20;
            $badges[] = ['id' => 'debt_free', 'name' => 'Debt Free', 'color' => 'bg-amber-500/20 text-amber-400 border-amber-500/30', 'icon' => 'Award'];
        }

        return [
            'score'  => min(100, $healthScore),
            'badges' => $badges,
        ];
    }
}
