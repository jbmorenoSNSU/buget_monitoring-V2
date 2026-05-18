<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Account;
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
    public function __construct(private ReportService $service) {}

    public function index(): Response
    {
        return Inertia::render('Reports/Index');
    }

    public function incomeExpense(Request $request): Response
    {
        $from = $request->get('from', now()->subMonths(5)->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->endOfMonth()->format('Y-m-d'));

        return Inertia::render('Reports/IncomeExpense', [
            'data' => $this->service->incomeVsExpense($from, $to),
            'filters' => compact('from', 'to'),
        ]);
    }

    public function categoryExpense(Request $request): Response
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        return Inertia::render('Reports/CategoryExpense', [
            'data' => $this->service->categoryExpense($month, $year),
            'filters' => compact('month', 'year'),
        ]);
    }

    public function accountStatement(Request $request): Response
    {
        $accountId = (int) $request->get('account_id', Account::first()?->id ?? 0);
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $data = $accountId ? $this->service->accountStatement($accountId, $from, $to) : null;

        return Inertia::render('Reports/AccountStatement', [
            'data' => $data,
            'filters' => compact('accountId', 'from', 'to'),
            'accounts' => Account::with('person')->orderBy('name')->get(['id', 'name', 'person_id']),
        ]);
    }

    public function budgetGoal(Request $request): Response
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        return Inertia::render('Reports/BudgetGoal', [
            'data' => $this->service->budgetGoalReport($month, $year),
            'filters' => compact('month', 'year'),
        ]);
    }

    public function calendar(Request $request): Response
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        return Inertia::render('Reports/Calendar', [
            'data' => $this->service->calendarReport($month, $year),
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
            'income-expense-pdf' => $this->exportPdf('pdf.income-expense', [
                'data' => $this->service->incomeVsExpense($request->get('from'), $request->get('to')),
                'title' => 'Income vs Expense Report',
            ], "income-expense-{$date}.pdf"),
            'category-expense-excel' => Excel::download(
                new CategoryExpenseExport((int)$request->get('month', now()->month), (int)$request->get('year', now()->year)),
                "category-expense-{$date}.xlsx"
            ),
            'category-expense-pdf' => $this->exportPdf('pdf.category-expense', [
                'data' => $this->service->categoryExpense((int)$request->get('month', now()->month), (int)$request->get('year', now()->year)),
                'title' => 'Expense by Category Report',
            ], "category-expense-{$date}.pdf"),
            'account-statement-excel' => Excel::download(
                new AccountStatementExport((int)$request->get('account_id'), $request->get('from'), $request->get('to')),
                "account-statement-{$date}.xlsx"
            ),
            'account-statement-pdf' => $this->exportPdf('pdf.account-statement', [
                'data' => $this->service->accountStatement((int)$request->get('account_id'), $request->get('from'), $request->get('to')),
                'title' => 'Account Statement Report',
                'account' => Account::with('person')->find($request->get('account_id')),
            ], "account-statement-{$date}.pdf"),
            'budget-goal-excel' => Excel::download(
                new BudgetGoalExport((int)$request->get('month', now()->month), (int)$request->get('year', now()->year)),
                "budget-goal-{$date}.xlsx"
            ),
            'budget-goal-pdf' => $this->exportPdf('pdf.budget-goal', [
                'data' => $this->service->budgetGoalReport((int)$request->get('month', now()->month), (int)$request->get('year', now()->year)),
                'title' => 'Budget Goals vs Actual Report',
            ], "budget-goal-{$date}.pdf"),
            default => abort(404),
        };
    }

    private function exportPdf(string $view, array $data, string $filename)
    {
        $data['generatedAt'] = now()->format('F j, Y g:i A');
        $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'landscape');
        return $pdf->download($filename);
    }
}
