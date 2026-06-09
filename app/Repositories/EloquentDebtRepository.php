<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\DebtRepositoryInterface;
use App\Models\Debt;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentDebtRepository implements DebtRepositoryInterface
{
    public function paginate(?int $person_id = null): LengthAwarePaginator
    {
        $query = Debt::query()->with('person');

        if ($person_id) {
            $query->where('person_id', $person_id);
        }

        return $query->orderBy('status')
            ->orderBy('principal_amount', 'desc')
            ->paginate(50);
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
}
