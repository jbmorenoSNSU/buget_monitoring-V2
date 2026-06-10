<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\AccountResource;
use App\Http\Resources\BudgetGoalResource;
use App\Http\Resources\TransactionResource;
use App\Interfaces\PersonRepositoryInterface;
use App\Services\AccountService;
use App\Services\BudgetGoalService;
use App\Services\DashboardService;
use App\Services\DebtService;
use App\Services\ReportService;
use App\Services\SavingsGoalService;
use App\Services\TransactionService;
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
        private DashboardService $dashboardService,
        private DebtService $debtService,
        private SavingsGoalService $savingsGoalService,
        private PersonRepositoryInterface $personRepository,
    ) {}

    public function index(Request $request): Response
    {
        $now = now();
        $month = $now->month;
        $year = $now->year;
        $person_id = $request->filled('person_id') ? (int) $request->get('person_id') : null;

        $stats = $this->dashboardService->getDashboardStats($month, $year, $person_id);

        return Inertia::render('Dashboard/Index', [
            'stats' => $stats,
            'filters' => [
                'persons' => $this->personRepository->all_active(),
                'selectedPersonId' => $person_id,
            ],
            'accounts' => AccountResource::collection($this->accountService->get_active($person_id)),
            'recentTransactions' => Inertia::defer(fn () => TransactionResource::collection(
                $this->transactionService->get_recent_transactions(15, $person_id)
            )),
            'chartsAndGoals' => Inertia::defer(fn () => [
                'budgetGoals' => BudgetGoalResource::collection(
                    $this->budgetGoalService->get_for_month($month, $year, $person_id)
                ),
                'savingsGoals' => $this->savingsGoalService->all($person_id),
                'activeDebts' => $this->debtService->get_active($person_id),
                'upcomingRecurring' => $this->dashboardService->getUpcomingRecurring($person_id),
                'chartData' => [
                    'sixMonths' => $this->reportService->last_6_months_chart($person_id),
                    'categoryExpense' => $this->reportService->category_expense($month, $year, $person_id),
                    'cashFlowForecast' => $this->dashboardService->generateCashFlowForecast($stats['totalBalance'], $person_id),
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
