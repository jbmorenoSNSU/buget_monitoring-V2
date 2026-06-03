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
     * Get all recurring transaction templates.
     *
     * @return Collection<int, RecurringTransaction>
     */
    public function get_all(): Collection
    {
        return $this->recurringRepository->all();
    }

    /**
     * Toggle the active status of a recurring transaction template.
     */
    public function toggle(RecurringTransaction $recurring): RecurringTransaction
    {
        return $this->recurringRepository->update($recurring, ['is_active' => ! $recurring->is_active]);
    }

    /**
     * Delete a recurring transaction template.
     */
    public function delete(RecurringTransaction $recurring): void
    {
        $this->recurringRepository->delete($recurring);
    }

    /**
     * Process and generate transactions that are due.
     *
     * @return int Count of transactions generated.
     */
    public function generate_due(): int
    {
        $count = 0;
        $due_recurrings = $this->recurringRepository->all_due();

        foreach ($due_recurrings as $recurring) {
            DB::transaction(function () use ($recurring) {
                // The transaction repository automatically applies the account balance effect
                $this->transactionRepository->create([
                    'account_id' => $recurring->account_id,
                    'category_id' => $recurring->category_id,
                    'type' => $recurring->type->value ?? $recurring->type,
                    'amount' => $recurring->amount,
                    'transaction_date' => $recurring->next_due_date->toDateString(),
                    'description' => $recurring->description.' (Auto)',
                    'recurring_id' => $recurring->id,
                ]);

                $next_date = $this->compute_next_date($recurring);
                $update_data = [
                    'last_generated_date' => now()->toDateString(),
                    'next_due_date' => $next_date->toDateString(),
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
     * Calculate the next due date based on the recurring interval frequency.
     */
    private function compute_next_date(RecurringTransaction $recurring): Carbon
    {
        $current = Carbon::parse($recurring->next_due_date);
        $freq = $recurring->frequency->value ?? $recurring->frequency;

        return match ($freq) {
            'daily' => $current->addDay(),
            'weekly' => $current->addWeek(),
            'monthly' => $current->addMonth(),
            'yearly' => $current->addYear(),
        };
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
}
