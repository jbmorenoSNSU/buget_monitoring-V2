<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Account;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecurringTransactionService
{
    public function getAll()
    {
        return RecurringTransaction::with(['account.person', 'category'])
            ->orderBy('next_due_date')->get();
    }

    public function create(array $data): RecurringTransaction
    {
        $data['next_due_date'] = $data['start_date'];
        return RecurringTransaction::create($data);
    }

    public function update(RecurringTransaction $recurring, array $data): RecurringTransaction
    {
        $recurring->update($data);
        return $recurring->fresh();
    }

    public function toggle(RecurringTransaction $recurring): RecurringTransaction
    {
        $recurring->update(['is_active' => !$recurring->is_active]);
        return $recurring;
    }

    public function delete(RecurringTransaction $recurring): void
    {
        $recurring->delete();
    }

    public function generateDue(): int
    {
        $count = 0;
        $dueRecurrings = RecurringTransaction::active()->due()->get();

        foreach ($dueRecurrings as $recurring) {
            DB::transaction(function () use ($recurring) {
                $transaction = Transaction::create([
                    'account_id' => $recurring->account_id,
                    'category_id' => $recurring->category_id,
                    'type' => $recurring->type->value ?? $recurring->type,
                    'amount' => $recurring->amount,
                    'transaction_date' => $recurring->next_due_date,
                    'description' => $recurring->description . ' (Auto)',
                    'recurring_id' => $recurring->id,
                ]);

                $account = Account::find($recurring->account_id);
                $type = $recurring->type->value ?? $recurring->type;
                if ($type === 'income') {
                    $account->increment('current_balance', (float)$recurring->amount);
                } else {
                    $account->decrement('current_balance', (float)$recurring->amount);
                }

                $nextDate = $this->computeNextDate($recurring);
                $updateData = [
                    'last_generated_date' => now()->toDateString(),
                    'next_due_date' => $nextDate,
                ];

                if ($recurring->end_date && $nextDate->gt($recurring->end_date)) {
                    $updateData['is_active'] = false;
                }

                $recurring->update($updateData);
            });
            $count++;
        }

        return $count;
    }

    private function computeNextDate(RecurringTransaction $recurring): Carbon
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

    public function getUpcoming(int $days = 7)
    {
        return RecurringTransaction::with(['account.person', 'category'])
            ->active()
            ->where('next_due_date', '<=', now()->addDays($days)->toDateString())
            ->orderBy('next_due_date')
            ->get();
    }
}
