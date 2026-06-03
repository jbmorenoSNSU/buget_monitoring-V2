<?php

declare(strict_types=1);

namespace App\Exports;

use App\Services\ReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class BudgetGoalExport implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private int $month, private int $year) {}

    public function array(): array
    {
        $service = app(ReportService::class);
        $data = $service->budgetGoalReport($this->month, $this->year);

        return array_map(fn ($row) => [
            $row['category_name'], number_format($row['limit_amount'], 2),
            number_format($row['actual_spent'], 2), number_format($row['variance'], 2),
            $row['percent'].'%',
        ], $data);
    }

    public function headings(): array
    {
        return ['Category', 'Budget Limit (₱)', 'Actual Spent (₱)', 'Variance (₱)', '% Used'];
    }

    public function title(): string
    {
        return 'Budget Goals vs Actual';
    }
}
