<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\RecurringTransactionRepositoryInterface;
use App\Models\RecurringTransaction;
use Illuminate\Database\Eloquent\Collection;

class EloquentRecurringTransactionRepository implements RecurringTransactionRepositoryInterface
{
    public function all(): Collection
    {
        return RecurringTransaction::with(['account:id,name', 'category:id,name,icon'])
            ->orderBy('next_due_date')
            ->get();
    }

    public function find(int $id): ?RecurringTransaction
    {
        return RecurringTransaction::with(['account:id,name', 'category:id,name,icon'])->find($id);
    }

    public function all_due(): Collection
    {
        return RecurringTransaction::active()->due()->get();
    }

    public function upcoming(int $days): Collection
    {
        return RecurringTransaction::with(['account.person:id,name', 'category:id,name,icon'])
            ->active()
            ->where('next_due_date', '<=', now()->addDays($days)->toDateString())
            ->orderBy('next_due_date')
            ->get();
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

    public function delete(RecurringTransaction $recurring): void
    {
        $recurring->delete();
    }
}
