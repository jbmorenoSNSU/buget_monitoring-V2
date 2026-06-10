<?php

declare(strict_types=1);

namespace App\Exports;

use App\Services\ReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CategoryExpenseExport implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private int $month, private int $year, private ?int $person_id = null) {}

    public function array(): array
    {
        $service = app(ReportService::class);
        $data = $service->category_expense($this->month, $this->year, $this->person_id);

        return array_map(fn ($row) => [
            $row['category_name'], number_format($row['amount'], 2), $row['percentage'].'%',
        ], $data);
    }

    public function headings(): array
    {
        return ['Category', 'Amount Spent (₱)', '% of Total'];
    }

    public function title(): string
    {
        return 'Expense by Category';
    }
}
