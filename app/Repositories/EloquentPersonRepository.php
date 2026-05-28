<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\PersonRepositoryInterface;
use App\Models\Person;
use Illuminate\Database\Eloquent\Collection;

class EloquentPersonRepository implements PersonRepositoryInterface
{
    public function all(): Collection
    {
        $start_of_month = now()->startOfMonth();
        $end_of_month   = now()->endOfMonth();

        return Person::withCount(['accounts' => fn ($q) => $q->where('is_active', true)])
            ->withSum(['accounts' => fn ($q) => $q->where('is_active', true)], 'current_balance')
            ->withSum(['transactions as income_this_month' => function ($q) use ($start_of_month, $end_of_month) {
                $q->where('transactions.type', 'income')
                  ->whereBetween('transactions.transaction_date', [$start_of_month, $end_of_month]);
            }], 'amount')
            ->withSum(['transactions as expense_this_month' => function ($q) use ($start_of_month, $end_of_month) {
                $q->where('transactions.type', 'expense')
                  ->whereBetween('transactions.transaction_date', [$start_of_month, $end_of_month]);
            }], 'amount')
            ->orderBy('name')
            ->get();
    }

    public function all_active(): Collection
    {
        return Person::active()->orderBy('name')->get(['id', 'name', 'color']);
    }

    public function find(int $id): ?Person
    {
        return Person::find($id);
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

    public function delete(Person $person): bool
    {
        return (bool) $person->delete();
    }

    public function has_accounts(Person $person): bool
    {
        return $person->accounts()->exists();
    }
}
