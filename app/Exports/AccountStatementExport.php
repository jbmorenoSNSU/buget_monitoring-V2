<?php

declare(strict_types=1);

namespace App\Exports;

use App\Services\ReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AccountStatementExport implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private int $accountId, private ?string $from = null, private ?string $to = null) {}

    public function array(): array
    {
        $service = app(ReportService::class);
        $data = $service->account_statement($this->accountId, $this->from, $this->to);

        return array_map(fn ($row) => [
            $row['date'], $row['description'], $row['category'], ucfirst($row['type']),
            number_format($row['amount'], 2), number_format($row['balance'], 2),
        ], $data['transactions']);
    }

    public function headings(): array
    {
        return ['Date', 'Description', 'Category', 'Type', 'Amount (₱)', 'Balance (₱)'];
    }

    public function title(): string
    {
        return 'Account Statement';
    }
}
