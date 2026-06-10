<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Interfaces\AccountRepositoryInterface;
use App\Jobs\ExportReportJob;
use App\Models\Export;
use App\Models\Person;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

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
        $person_id = $request->get('person_id') ? (int) $request->get('person_id') : null;

        return Inertia::render('Reports/IncomeExpense', [
            'data' => $this->service->income_vs_expense($from, $to, $person_id),
            'filters' => compact('from', 'to', 'person_id'),
            'persons' => Person::select('id', 'name')->get(),
        ]);
    }

    public function category_expense(Request $request): Response
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $person_id = $request->get('person_id') ? (int) $request->get('person_id') : null;

        return Inertia::render('Reports/CategoryExpense', [
            'data' => $this->service->category_expense($month, $year, $person_id),
            'filters' => compact('month', 'year', 'person_id'),
            'persons' => Person::select('id', 'name')->get(),
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
            'accounts' => $this->accountRepository->all()->load('person')->map(fn ($acc) => [
                'id' => $acc->id,
                'name' => $acc->name,
                'person' => $acc->person ? ['name' => $acc->person->name] : null,
            ]),
        ]);
    }

    public function budget_goal(Request $request): Response
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $person_id = $request->get('person_id') ? (int) $request->get('person_id') : null;

        return Inertia::render('Reports/BudgetGoal', [
            'data' => $this->service->budget_goal_report($month, $year, $person_id),
            'filters' => compact('month', 'year', 'person_id'),
            'persons' => Person::select('id', 'name')->get(),
        ]);
    }

    public function calendar(Request $request): Response
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $person_id = $request->get('person_id') ? (int) $request->get('person_id') : null;
        $account_id = $request->get('account_id') ? (int) $request->get('account_id') : null;

        return Inertia::render('Reports/Calendar', [
            'data' => $this->service->calendar_report($month, $year, $person_id, $account_id),
            'filters' => compact('month', 'year', 'person_id', 'account_id'),
            'persons' => Person::select('id', 'name')->get(),
            'accounts' => $this->accountRepository->all()->map(fn ($acc) => [
                'id' => $acc->id,
                'name' => $acc->name,
                'person_id' => $acc->person_id,
            ]),
        ]);
    }

    public function settlements(Request $request): Response
    {
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        return Inertia::render('Reports/Settlements', [
            'data' => $this->service->settlement_report($from, $to),
            'filters' => compact('from', 'to'),
        ]);
    }

    public function year_in_review(Request $request): Response
    {
        $year = (int) $request->get('year', now()->year);

        return Inertia::render('Reports/YearInReview', [
            'data' => $this->service->year_in_review($year),
            'filters' => compact('year'),
        ]);
    }

    public function forecasting(): Response
    {
        return Inertia::render('Reports/Forecasting', [
            'data' => $this->service->cashflow_projection(),
        ]);
    }

    public function export(Request $request, string $type)
    {
        // Example $type: 'income-expense-excel' or 'income-expense-pdf'
        $parts = explode('-', $type);
        $format = array_pop($parts); // 'excel' or 'pdf'
        $reportType = implode('-', $parts);

        if ($format === 'excel') {
            $format = 'xlsx';
        }

        // Create the Export record
        $export = Export::create([
            'user_id' => $request->user()->id ?? 1, // Fallback to 1 for simplicity if no auth
            'type' => $reportType,
            'format' => $format,
        ]);

        // Dispatch job
        ExportReportJob::dispatch($export, $request->all());

        return redirect()->route('downloads.index')
            ->with('success', 'Your export has started in the background. It will appear here when ready.');
    }
}
