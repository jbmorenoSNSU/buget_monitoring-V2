<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\RecurringTransactionService;
use Illuminate\Console\Command;

class GenerateRecurringTransactions extends Command
{
    protected $signature = 'recurring:generate';

    protected $description = 'Generate due recurring transactions';

    public function handle(RecurringTransactionService $service): int
    {
        $count = $service->generate_due();
        $this->info("Generated {$count} recurring transaction(s).");

        return self::SUCCESS;
    }
}
