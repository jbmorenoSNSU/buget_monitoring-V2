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

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'totalBalance' => $this->accountService->get_total_balance($person_id),
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
                'upcomingRecurring' => $this->recurringService->get_upcoming(30),
                'chartData' => [
                    'sixMonths' => $this->reportService->last_6_months_chart($person_id),
                    'categoryExpense' => $this->reportService->category_expense($month, $year, $person_id),
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
