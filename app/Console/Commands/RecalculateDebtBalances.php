<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Debt;
use Illuminate\Console\Command;

/**
 * Manually correct a debt's current balance to match your lender's statement.
 *
 * Use this whenever the stored balance drifts from reality (e.g. after
 * importing old data, or if you forgot to log a payment).
 *
 * Usage:
 *   php artisan debts:set-balance          — interactive, lists all debts
 *   php artisan debts:set-balance 3 243821.58  — direct (ID + amount)
 */
class RecalculateDebtBalances extends Command
{
    protected $signature = 'debts:set-balance {id? : Debt ID} {amount? : Correct balance}';

    protected $description = 'Manually set a debt\'s current balance to match your lender\'s statement.';

    public function handle(): int
    {
        $debts = Debt::orderBy('name')->get(['id', 'name', 'principal_amount', 'status']);

        if ($debts->isEmpty()) {
            $this->warn('No debts found.');

            return self::SUCCESS;
        }

        // Show current state.
        $this->table(
            ['ID', 'Name', 'Status', 'Stored Balance'],
            $debts->map(fn ($d) => [$d->id, $d->name, $d->status, number_format((float) $d->principal_amount, 2)])->toArray()
        );

        $id = $this->argument('id') ?? $this->ask('Debt ID to correct');
        $amount = $this->argument('amount') ?? $this->ask('Correct balance (from lender statement)');

        $debt = Debt::find((int) $id);

        if (! $debt) {
            $this->error("Debt ID {$id} not found.");

            return self::FAILURE;
        }

        $old = (float) $debt->principal_amount;
        $new = round((float) $amount, 2);

        $debt->update(['principal_amount' => $new]);

        $this->info("✓ {$debt->name}: {$old} → {$new}");

        return self::SUCCESS;
    }
}
