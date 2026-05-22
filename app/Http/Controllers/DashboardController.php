<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\AccountResource;
use App\Http\Resources\BudgetGoalResource;
use App\Http\Resources\TransactionResource;
use App\Models\Person;
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
    ) {}

    public function index(Request $request): Response
    {
        $now = now();
        $month = $now->month;
        $year = $now->year;
        $personId = $request->filled('person_id') ? (int) $request->get('person_id') : null;

        $dailyTrend = $this->reportService->dailySpendingTrend($month, $year, $personId);

        return Inertia::render('Dashboard/Index', [
            'totalBalance' => $this->accountService->getTotalBalance($personId),
            'monthlyIncome' => $this->transactionService->getMonthlyIncome($month, $year, $personId),
            'monthlyExpense' => $this->transactionService->getMonthlyExpense($month, $year, $personId),
            'accounts' => AccountResource::collection($this->accountService->getActive($personId)),
            'recentTransactions' => TransactionResource::collection($this->transactionService->getRecentTransactions(10, $personId)),
            'budgetGoals' => BudgetGoalResource::collection($this->budgetGoalService->getForMonth($month, $year, $personId)),
            'upcomingRecurring' => $this->recurringService->getUpcoming(30),
            'chartData' => [
                'sixMonths' => $this->reportService->last6MonthsChart($personId),
                'categoryExpense' => $this->reportService->categoryExpense($month, $year, $personId),
                'spendingTrend' => [
                    'daily' => $dailyTrend,
                    'weekly' => $this->reportService->weeklySpendingTrend($personId),
                    'monthly' => $this->reportService->yearlySpendingTrend($year, $personId),
                ],
            ],
            'persons' => Person::active()->orderBy('name')->get(['id', 'name', 'color']),
            'selectedPersonId' => $personId,
        ]);
    }
}
