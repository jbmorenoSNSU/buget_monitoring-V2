<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\RecurringTransactionRepositoryInterface;
use App\Interfaces\TransactionRepositoryInterface;
use App\Models\RecurringTransaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service class handling template automation for recurring financial transactions.
 */
class RecurringTransactionService
{
    /**
     * Create a new RecurringTransactionService instance.
     */
    public function __construct(
        private RecurringTransactionRepositoryInterface $recurringRepository,
        private TransactionRepositoryInterface $transactionRepository
    ) {}

    /**
     * Process and generate transactions that are due.
     * If advance_credit covers the full amount, the cycle is skipped (no ledger
     * entry) and the credit is carried forward. Otherwise the generated amount
     * is reduced by the credit.
     *
     * @return int Count of transactions generated.
     */
    public function generate_due(): int
    {
        $count = 0;
        $due_recurrings = $this->recurringRepository->all_due();

        foreach ($due_recurrings as $recurring) {
            DB::transaction(function () use ($recurring) {
                $credit = (float) $recurring->advance_credit;
                $full_amount = (float) $recurring->amount;
                $effective_amount = max(0.0, $full_amount - $credit);
                $leftover_credit = max(0.0, $credit - $full_amount);

                // ponytail: if credit fully covers this cycle, skip ledger entry entirely.
                // The user already paid — generating a ₱0 expense would just be noise.
                if ($effective_amount > 0) {
                    $this->transactionRepository->create([
                        'account_id' => $recurring->account_id,
                        'category_id' => $recurring->category_id,
                        'type' => $recurring->type->value ?? $recurring->type,
                        'amount' => $effective_amount,
                        'transaction_date' => $recurring->next_due_date->toDateString(),
                        'description' => $recurring->description.' (Auto'
                            .($credit > 0 ? ', ₱'.number_format($credit, 2).' advance applied' : '')
                            .')',
                        'recurring_id' => $recurring->id,
                        'debt_id' => $recurring->debt_id,
                    ]);
                }

                $next_date = $this->compute_next_date($recurring);
                $update_data = [
                    'last_generated_date' => now()->toDateString(),
                    'next_due_date' => $next_date->toDateString(),
                    'advance_credit' => $leftover_credit,
                ];

                if ($recurring->end_date && $next_date->gt($recurring->end_date)) {
                    $update_data['is_active'] = false;
                }

                $this->recurringRepository->update($recurring, $update_data);
            });
            $count++;
        }

        return $count;
    }

    /**
     * Reconcile an advance payment against a debt's recurring transaction.
     *
     * Called after creating a manual debt-linked expense. If the accumulated
     * advance credit meets or exceeds the recurring amount, the due date
     * advances (potentially multiple cycles for large overpayments).
     */
    public function reconcile_advance_payment(int $debt_id, float $amount_paid): void
    {
        $recurring = $this->recurringRepository->find_active_by_debt($debt_id);
        if (! $recurring) {
            return;
        }

        $credit = (float) $recurring->advance_credit + $amount_paid;
        $full_amount = (float) $recurring->amount;

        // ponytail: loop handles overpayments that cover multiple cycles.
        // ceiling — a ₱1M payment on a ₱100/mo recurring would loop 10,000 times.
        // For personal finance this is unrealistic; upgrade to division if needed.
        $next_due = Carbon::parse($recurring->next_due_date);
        $cycles_advanced = 0;

        while ($credit >= $full_amount) {
            $credit -= $full_amount;
            $next_due = $this->compute_next_date_from($next_due, $recurring);
            $cycles_advanced++;
        }

        $update_data = ['advance_credit' => $credit];

        if ($cycles_advanced > 0) {
            $update_data['next_due_date'] = $next_due->toDateString();
            $update_data['last_generated_date'] = now()->toDateString();
        }

        $this->recurringRepository->update($recurring, $update_data);
    }

    /**
     * Reverse advance credit when a debt-linked transaction is deleted or reduced.
     */
    public function reverse_advance_credit(int $debt_id, float $amount): void
    {
        $recurring = $this->recurringRepository->find_active_by_debt($debt_id);
        if (! $recurring) {
            return;
        }

        $new_credit = max(0.0, (float) $recurring->advance_credit - $amount);
        $this->recurringRepository->update($recurring, ['advance_credit' => $new_credit]);
    }

    /**
     * Calculate the next due date based on the recurring interval frequency.
     */
    private function compute_next_date(RecurringTransaction $recurring): Carbon
    {
        return $this->compute_next_date_from(
            Carbon::parse($recurring->next_due_date),
            $recurring
        );
    }

    /**
     * Calculate next date from a given starting date using the recurring frequency.
     */
    private function compute_next_date_from(Carbon $current, RecurringTransaction $recurring): Carbon
    {
        $freq = $recurring->frequency->value ?? $recurring->frequency;

        return match ($freq) {
            'daily' => $current->copy()->addDay(),
            'weekly' => $current->copy()->addWeek(),
            'monthly' => $current->copy()->addMonth(),
            'yearly' => $current->copy()->addYear(),
        };
    }

    /**
     * Get all recurring transactions (active and inactive).
     *
     * @return Collection<int, RecurringTransaction>
     */
    public function get_all(): Collection
    {
        return $this->recurringRepository->all();
    }

    /**
     * Get upcoming recurring transactions due in the specified number of days.
     *
     * @return Collection<int, RecurringTransaction>
     */
    public function get_upcoming(int $days = 7): Collection
    {
        return $this->recurringRepository->upcoming($days);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): RecurringTransaction
    {
        return $this->recurringRepository->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(RecurringTransaction $recurring, array $data): RecurringTransaction
    {
        return $this->recurringRepository->update($recurring, $data);
    }

    public function delete(RecurringTransaction $recurring): void
    {
        $this->recurringRepository->delete($recurring);
    }

    public function toggle(RecurringTransaction $recurring): void
    {
        $this->recurringRepository->update($recurring, ['is_active' => ! $recurring->is_active]);
    }
}

