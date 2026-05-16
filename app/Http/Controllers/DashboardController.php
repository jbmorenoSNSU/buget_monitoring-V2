<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\AccountResource;
use App\Http\Resources\BudgetGoalResource;
use App\Http\Resources\TransactionResource;
use App\Services\AccountService;
use App\Services\BudgetGoalService;
use App\Services\RecurringTransactionService;
use App\Services\ReportService;
use App\Services\TransactionService;
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
    ) {}

    public function index(): Response
    {
        $now = now();
        $month = $now->month;
        $year = $now->year;

        return Inertia::render('Dashboard/Index', [
            'totalBalance' => $this->accountService->getTotalBalance(),
            'monthlyIncome' => $this->transactionService->getMonthlyIncome($month, $year),
            'monthlyExpense' => $this->transactionService->getMonthlyExpense($month, $year),
            'accounts' => AccountResource::collection($this->accountService->getActive()),
            'recentTransactions' => TransactionResource::collection($this->transactionService->getRecentTransactions()),
            'budgetGoals' => BudgetGoalResource::collection($this->budgetGoalService->getForMonth($month, $year)),
            'upcomingRecurring' => $this->recurringService->getUpcoming(7),
            'chartData' => [
                'sixMonths' => $this->reportService->last6MonthsChart(),
                'categoryExpense' => $this->reportService->categoryExpense($month, $year),
                'dailySpending' => $this->reportService->dailySpendingTrend($month, $year),
            ],
        ]);
    }
}
