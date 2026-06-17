<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Exports\AccountStatementExport;
use App\Exports\BudgetGoalExport;
use App\Exports\CategoryExpenseExport;
use App\Exports\IncomeExpenseExport;
use App\Interfaces\AccountRepositoryInterface;
use App\Models\Export;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExportReportJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 300;

    public $tries = 3;

    /** @var int[] */
    public $backoff = [30, 60, 120];

    public function __construct(
        public Export $exportRecord,
        public array $params
    ) {}

    public function handle(ReportService $reportService, AccountRepositoryInterface $accountRepository): void
    {
        $this->exportRecord->update(['status' => 'processing']);

        try {
            $fileName = $this->exportRecord->type.'-'.now()->format('Y-m-d_H-i-s').'.'.$this->exportRecord->format;
            $filePath = 'exports/'.$fileName;

            if ($this->exportRecord->format === 'xlsx') {
                $exportObj = match ($this->exportRecord->type) {
                    'income-expense' => new IncomeExpenseExport($this->params['from'] ?? null, $this->params['to'] ?? null, $this->params['person_id'] ?? null),
                    'category-expense' => new CategoryExpenseExport($this->params['month'] ?? now()->month, $this->params['year'] ?? now()->year, $this->params['person_id'] ?? null),
                    'account-statement' => new AccountStatementExport($this->params['account_id'], $this->params['from'] ?? null, $this->params['to'] ?? null),
                    'budget-goal' => new BudgetGoalExport($this->params['month'] ?? now()->month, $this->params['year'] ?? now()->year, $this->params['person_id'] ?? null),
                };

                Excel::store($exportObj, $filePath, 'public');
            } else {
                // PDF
                $viewData = match ($this->exportRecord->type) {
                    'income-expense' => [
                        'view' => 'pdf.income-expense',
                        'data' => [
                            'data' => $reportService->income_vs_expense($this->params['from'] ?? null, $this->params['to'] ?? null, $this->params['person_id'] ?? null),
                            'title' => 'Income vs Expense Report',
                        ],
                    ],
                    'category-expense' => [
                        'view' => 'pdf.category-expense',
                        'data' => [
                            'data' => $reportService->category_expense($this->params['month'] ?? now()->month, $this->params['year'] ?? now()->year, $this->params['person_id'] ?? null),
                            'title' => 'Expense by Category Report',
                        ],
                    ],
                    'account-statement' => [
                        'view' => 'pdf.account-statement',
                        'data' => [
                            'data' => $reportService->account_statement($this->params['account_id'], $this->params['from'] ?? null, $this->params['to'] ?? null),
                            'title' => 'Account Statement Report',
                            'account' => $accountRepository->find($this->params['account_id']),
                        ],
                    ],
                    'budget-goal' => [
                        'view' => 'pdf.budget-goal',
                        'data' => [
                            'data' => $reportService->budget_goal_report($this->params['month'] ?? now()->month, $this->params['year'] ?? now()->year, $this->params['person_id'] ?? null),
                            'title' => 'Budget Goals vs Actual Report',
                        ],
                    ],
                };

                $viewData['data']['generated_at'] = now()->format('F j, Y g:i A');
                $pdf = Pdf::loadView($viewData['view'], $viewData['data'])->setPaper('a4', 'landscape');
                Storage::disk('public')->put($filePath, $pdf->output());
            }

            $this->exportRecord->update([
                'status' => 'completed',
                'file_name' => $fileName,
                'file_path' => $filePath,
            ]);

        } catch (\Exception $e) {
            $this->exportRecord->update([
                'status' => 'failed',
                'error'  => $e->getMessage(),
            ]);

            Log::error('ExportReportJob failed', [
                'export_id' => $this->exportRecord->id,
                'type'      => $this->exportRecord->type,
                'format'    => $this->exportRecord->format,
                'error'     => $e->getMessage(),
            ]);
        }
    }
}
