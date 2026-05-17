<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Person;
use Illuminate\Database\Eloquent\Collection;

class PersonService
{
    /**
     * Get all persons with aggregated account data.
     * Uses withCount and withSum for optimized single-query aggregation.
     */
    public function getAll(): Collection
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        return Person::withCount(['accounts' => fn ($q) => $q->where('is_active', true)])
            ->withSum(['accounts' => fn ($q) => $q->where('is_active', true)], 'current_balance')
            ->withSum(['transactions as income_this_month' => function ($q) use ($startOfMonth, $endOfMonth) {
                $q->where('transactions.type', 'income')
                  ->whereBetween('transactions.transaction_date', [$startOfMonth, $endOfMonth]);
            }], 'amount')
            ->withSum(['transactions as expense_this_month' => function ($q) use ($startOfMonth, $endOfMonth) {
                $q->where('transactions.type', 'expense')
                  ->whereBetween('transactions.transaction_date', [$startOfMonth, $endOfMonth]);
            }], 'amount')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get only active persons (for dropdowns).
     */
    public function getActive(): Collection
    {
        return Person::active()->orderBy('name')->get(['id', 'name', 'color']);
    }

    public function create(array $data): Person
    {
        return Person::create($data);
    }

    public function update(Person $person, array $data): Person
    {
        $person->update($data);
        return $person->fresh();
    }

    public function canDelete(Person $person): bool
    {
        return $person->accounts()->count() === 0;
    }

    public function delete(Person $person): bool
    {
        if (!$this->canDelete($person)) {
            return false;
        }
        $person->delete();
        return true;
    }
}
