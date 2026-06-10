<?php

declare(strict_types=1);

namespace App\Exports;

use App\Services\ReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class IncomeExpenseExport implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private string $from, private string $to, private ?int $person_id = null) {}

    public function array(): array
    {
        $service = app(ReportService::class);
        $data = $service->income_vs_expense($this->from, $this->to, $this->person_id);

        return array_map(fn ($row) => [
            $row['label'], number_format($row['income'], 2), number_format($row['expense'], 2), number_format($row['net'], 2),
        ], $data);
    }

    public function headings(): array
    {
        return ['Month', 'Income (₱)', 'Expense (₱)', 'Net Savings (₱)'];
    }

    public function title(): string
    {
        return 'Income vs Expense';
    }
}
