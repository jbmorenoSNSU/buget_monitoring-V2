<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Interfaces\AccountRepositoryInterface;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\IncomeExpenseExport;
use App\Exports\CategoryExpenseExport;
use App\Exports\AccountStatementExport;
use App\Exports\BudgetGoalExport;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $service,
        private AccountRepositoryInterface $accountRepository
    ) {}

    public function index(): Response
    {
        return Inertia::render('Reports/Index');
    }

    public function income_expense(Request $request): Response
    {
        $from = $request->get('from', now()->subMonths(5)->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->endOfMonth()->format('Y-m-d'));

        return Inertia::render('Reports/IncomeExpense', [
            'data' => $this->service->income_vs_expense($from, $to),
            'filters' => compact('from', 'to'),
        ]);
    }

    public function category_expense(Request $request): Response
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        return Inertia::render('Reports/CategoryExpense', [
            'data' => $this->service->category_expense($month, $year),
            'filters' => compact('month', 'year'),
        ]);
    }

    public function account_statement(Request $request): Response
    {
        $firstAccount = $this->accountRepository->all()->first();
        $account_id = (int) $request->get('account_id', $firstAccount?->id ?? 0);
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $data = $account_id ? $this->service->account_statement($account_id, $from, $to) : null;

        return Inertia::render('Reports/AccountStatement', [
            'data' => $data,
            'filters' => compact('account_id', 'from', 'to'),
            'accounts' => $this->accountRepository->all()->map(fn ($acc) => [
                'id' => $acc->id,
                'name' => $acc->name,
                'person_id' => $acc->person_id,
            ]),
        ]);
    }

    public function budget_goal(Request $request): Response
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        return Inertia::render('Reports/BudgetGoal', [
            'data' => $this->service->budget_goal_report($month, $year),
            'filters' => compact('month', 'year'),
        ]);
    }

    public function calendar(Request $request): Response
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        return Inertia::render('Reports/Calendar', [
            'data' => $this->service->calendar_report($month, $year),
            'filters' => compact('month', 'year'),
        ]);
    }

    public function export(Request $request, string $type)
    {
        $date = now()->format('Y-m-d');

        return match ($type) {
            'income-expense-excel' => Excel::download(
                new IncomeExpenseExport($request->get('from'), $request->get('to')),
                "income-expense-{$date}.xlsx"
            ),
            'income-expense-pdf' => $this->export_pdf('pdf.income-expense', [
                'data' => $this->service->income_vs_expense($request->get('from'), $request->get('to')),
                'title' => 'Income vs Expense Report',
            ], "income-expense-{$date}.pdf"),
            'category-expense-excel' => Excel::download(
                new CategoryExpenseExport((int)$request->get('month', now()->month), (int)$request->get('year', now()->year)),
                "category-expense-{$date}.xlsx"
            ),
            'category-expense-pdf' => $this->export_pdf('pdf.category-expense', [
                'data' => $this->service->category_expense((int)$request->get('month', now()->month), (int)$request->get('year', now()->year)),
                'title' => 'Expense by Category Report',
            ], "category-expense-{$date}.pdf"),
            'account-statement-excel' => Excel::download(
                new AccountStatementExport((int)$request->get('account_id'), $request->get('from'), $request->get('to')),
                "account-statement-{$date}.xlsx"
            ),
            'account-statement-pdf' => $this->export_pdf('pdf.account-statement', [
                'data' => $this->service->account_statement((int)$request->get('account_id'), $request->get('from'), $request->get('to')),
                'title' => 'Account Statement Report',
                'account' => $this->accountRepository->find((int) $request->get('account_id')),
            ], "account-statement-{$date}.pdf"),
            'budget-goal-excel' => Excel::download(
                new BudgetGoalExport((int)$request->get('month', now()->month), (int)$request->get('year', now()->year)),
                "budget-goal-{$date}.xlsx"
            ),
            'budget-goal-pdf' => $this->export_pdf('pdf.budget-goal', [
                'data' => $this->service->budget_goal_report((int)$request->get('month', now()->month), (int)$request->get('year', now()->year)),
                'title' => 'Budget Goals vs Actual Report',
            ], "budget-goal-{$date}.pdf"),
            default => abort(404),
        };
    }

    private function export_pdf(string $view, array $data, string $filename)
    {
        $data['generated_at'] = now()->format('F j, Y g:i A');
        $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'landscape');
        return $pdf->download($filename);
    }
}
