<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Interfaces\AccountRepositoryInterface;
use App\Interfaces\PersonRepositoryInterface;
use App\Jobs\ExportReportJob;
use App\Models\Export;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles HTTP actions for financial report views and export dispatching.
 */
class ReportController extends Controller
{
    public function __construct(
        private ReportService $service,
        private AccountRepositoryInterface $accountRepository,
        private PersonRepositoryInterface $personRepository
    ) {}

    /**
     * Display the reports index page.
     */
    public function index(): Response
    {
        return Inertia::render('Reports/Index');
    }

    /**
     * Display the income vs. expense report.
     */
    public function income_expense(Request $request): Response
    {
        $from = $request->get('from', now()->subMonths(5)->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->endOfMonth()->format('Y-m-d'));
        $person_id = $request->get('person_id') ? (int) $request->get('person_id') : null;

        return Inertia::render('Reports/IncomeExpense', [
            'data' => $this->service->income_vs_expense($from, $to, $person_id),
            'filters' => compact('from', 'to', 'person_id'),
            'persons' => $this->personRepository->all_active(),
        ]);
    }

    /**
     * Display the expense breakdown by category report.
     */
    public function category_expense(Request $request): Response
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $person_id = $request->get('person_id') ? (int) $request->get('person_id') : null;

        return Inertia::render('Reports/CategoryExpense', [
            'data' => $this->service->category_expense($month, $year, $person_id),
            'filters' => compact('month', 'year', 'person_id'),
            'persons' => $this->personRepository->all_active(),
        ]);
    }

    /**
     * Display the account statement report.
     */
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

    /**
     * Display the budget goal performance report.
     */
    public function budget_goal(Request $request): Response
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $person_id = $request->get('person_id') ? (int) $request->get('person_id') : null;

        return Inertia::render('Reports/BudgetGoal', [
            'data' => $this->service->budget_goal_report($month, $year, $person_id),
            'filters' => compact('month', 'year', 'person_id'),
            'persons' => $this->personRepository->all_active(),
        ]);
    }

    /**
     * Display the transaction calendar report.
     */
    public function calendar(Request $request): Response
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $person_id = $request->get('person_id') ? (int) $request->get('person_id') : null;
        $account_id = $request->get('account_id') ? (int) $request->get('account_id') : null;

        return Inertia::render('Reports/Calendar', [
            'data' => $this->service->calendar_report($month, $year, $person_id, $account_id),
            'filters' => compact('month', 'year', 'person_id', 'account_id'),
            'persons' => $this->personRepository->all_active(),
            'accounts' => $this->accountRepository->all()->map(fn ($acc) => [
                'id' => $acc->id,
                'name' => $acc->name,
                'person_id' => $acc->person_id,
            ]),
        ]);
    }

    /**
     * Display the year-in-review summary report.
     */
    public function year_in_review(Request $request): Response
    {
        $year = (int) $request->get('year', now()->year);

        return Inertia::render('Reports/YearInReview', [
            'data' => $this->service->year_in_review($year),
            'filters' => compact('year'),
        ]);
    }

    /**
     * Display the cash flow forecasting report.
     */
    public function forecasting(): Response
    {
        return Inertia::render('Reports/Forecasting', [
            'data' => $this->service->cashflow_projection(),
        ]);
    }

    /**
     * Dispatch a background export job for the given report type and format.
     */
    public function export(Request $request, string $type)
    {
        $parts = explode('-', $type);
        $format = array_pop($parts);
        $reportType = implode('-', $parts);

        if ($format === 'excel') {
            $format = 'xlsx';
        }

        // Single-user personal app — no auth system installed
        $export = Export::create([
            'user_id' => 1,
            'type' => $reportType,
            'format' => $format,
        ]);

        ExportReportJob::dispatch($export, $request->all());

        return redirect()->route('downloads.index')
            ->with('success', 'Your export has started in the background. It will appear here when ready.');
    }
}
