<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\AccountResource;
use App\Http\Resources\BudgetGoalResource;
use App\Http\Resources\TransactionResource;
use App\Interfaces\PersonRepositoryInterface;
use App\Services\AccountService;
use App\Services\BudgetGoalService;
use App\Services\RecurringTransactionService;
use App\Services\ReportService;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private AccountService $accountService,
        private TransactionService $transactionService,
        private BudgetGoalService $budgetGoalService,
        private ReportService $reportService,
        private RecurringTransactionService $recurringService,
        private PersonRepositoryInterface $personRepository,
    ) {}

    public function index(Request $request): Response
    {
        $now = now();
        $month = $now->month;
        $year = $now->year;
        $person_id = $request->filled('person_id') ? (int) $request->get('person_id') : null;

        $totalBalance = $this->accountService->get_total_balance($person_id);

        // Safe to spend calculation
        $budgetGoals = $this->budgetGoalService->get_for_month($month, $year, $person_id);
        $remainingBudgets = $budgetGoals->sum(fn ($g) => max(0, $g->remaining));

        $upcomingRecurring = $this->recurringService->get_upcoming(30);
        $upcomingExpenses = $upcomingRecurring
            ->filter(function ($r) use ($person_id) {
                if ($person_id && $r->account && $r->account->person_id !== $person_id) {
                    return false;
                }
                $type = $r->type->value ?? $r->type;

                return $type === 'expense';
            })
            ->sum('amount');

        $safeToSpend = $totalBalance - $remainingBudgets - $upcomingExpenses;

        // Cash Flow Forecast (30 days)
        $forecast = [];
        $projectedBalance = $totalBalance;
        $today = now()->startOfDay();
        $dailyHits = array_fill(0, 31, 0);
        $allRecurring = $this->recurringService->get_all();

        foreach ($allRecurring as $rec) {
            if (! $rec->is_active) {
                continue;
            }
            if ($person_id && $rec->account && $rec->account->person_id !== $person_id) {
                continue;
            }

            $nextDue = Carbon::parse($rec->next_due_date)->startOfDay();
            $endDate = $rec->end_date ? Carbon::parse($rec->end_date)->startOfDay() : null;
            $amount = (float) $rec->amount;
            $type = $rec->type->value ?? $rec->type;
            $amount = $type === 'income' ? $amount : -$amount;

            $hitDate = $nextDue->copy();
            $freq = $rec->frequency->value ?? $rec->frequency;

            // Generate hits up to 30 days in the future
            while ($hitDate->diffInDays($today, false) >= -30) {
                // If it's past due or due today, treat it as hitting today (day 0)
                $daysFromNow = max(0, (int) $today->diffInDays($hitDate, false));

                if ($daysFromNow <= 30 && (! $endDate || $hitDate->lte($endDate))) {
                    $dailyHits[$daysFromNow] += $amount;
                }

                match ($freq) {
                    'daily' => $hitDate->addDay(),
                    'weekly' => $hitDate->addWeek(),
                    'monthly' => $hitDate->addMonth(),
                    'yearly' => $hitDate->addYear(),
                };
            }
        }

        for ($i = 0; $i <= 30; $i++) {
            $projectedBalance += $dailyHits[$i];
            $forecast[] = [
                'date' => now()->addDays($i)->format('M d'),
                'balance' => $projectedBalance,
            ];
        }

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'totalBalance' => $totalBalance,
                'safeToSpend' => $safeToSpend,
                'monthlyIncome' => $this->transactionService->get_monthly_income($month, $year, $person_id),
                'monthlyExpense' => $this->transactionService->get_monthly_expense($month, $year, $person_id),
            ],
            'filters' => [
                'persons' => $this->personRepository->all_active(),
                'selectedPersonId' => $person_id,
            ],
            'accounts' => AccountResource::collection($this->accountService->get_active($person_id)),
            'recentTransactions' => Inertia::defer(fn () => TransactionResource::collection(
                $this->transactionService->get_recent_transactions(10, $person_id)
            )),
            'chartsAndGoals' => Inertia::defer(fn () => [
                'budgetGoals' => BudgetGoalResource::collection(
                    $this->budgetGoalService->get_for_month($month, $year, $person_id)
                ),
                'upcomingRecurring' => $upcomingRecurring,
                'chartData' => [
                    'sixMonths' => $this->reportService->last_6_months_chart($person_id),
                    'categoryExpense' => $this->reportService->category_expense($month, $year, $person_id),
                    'cashFlowForecast' => $forecast,
                    'spendingTrend' => [
                        'daily' => $this->reportService->daily_spending_trend($month, $year, $person_id),
                        'weekly' => $this->reportService->weekly_spending_trend($person_id),
                        'monthly' => $this->reportService->yearly_spending_trend($year, $person_id),
                    ],
                ],
            ]),
        ]);
    }
}
