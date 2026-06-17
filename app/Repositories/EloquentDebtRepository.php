<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\DebtRepositoryInterface;
use App\Models\Debt;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentDebtRepository implements DebtRepositoryInterface
{
    public function paginate(?int $person_id = null): CursorPaginator
    {
        $query = Debt::query()->with('person:id,name,color');

        if ($person_id) {
            $query->where('person_id', $person_id);
        }

        return $query->orderBy('status')
            ->orderBy('principal_amount', 'desc')
            ->cursorPaginate(50);
    }

    public function get_active(?int $person_id = null): Collection
    {
        $query = Debt::query()->with('person:id,name,color')->where('status', 'active');

        if ($person_id) {
            $query->where('person_id', $person_id);
        }

        return $query->orderBy('principal_amount', 'desc')->get();
    }

    public function all(): Collection
    {
        return Debt::orderBy('name')->get(['id', 'name', 'person_id', 'principal_amount', 'status']);
    }

    public function find(int $id): Debt
    {
        return Debt::findOrFail($id);
    }

    public function count_active(?int $person_id = null): int
    {
        $query = Debt::where('status', 'active');

        if ($person_id) {
            $query->where('person_id', $person_id);
        }

        return $query->count();
    }

    public function create(array $data): Debt
    {
        return Debt::create($data);
    }

    public function update(Debt $debt, array $data): Debt
    {
        $debt->update($data);

        return $debt->fresh();
    }

    public function delete(Debt $debt): void
    {
        $debt->delete();
    }

    public function increment_principal(int $id, float $amount): void
    {
        Debt::findOrFail($id)->increment('principal_amount', $amount);
    }

    public function decrement_principal(int $id, float $amount): void
    {
        Debt::findOrFail($id)->decrement('principal_amount', $amount);
    }
}
