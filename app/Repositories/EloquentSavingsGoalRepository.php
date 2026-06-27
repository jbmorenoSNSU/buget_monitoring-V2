<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\SavingsGoalRepositoryInterface;
use App\Models\SavingsGoal;
use Illuminate\Database\Eloquent\Collection;

class EloquentSavingsGoalRepository implements SavingsGoalRepositoryInterface
{
    public function all(?int $person_id = null): Collection
    {
        $query = SavingsGoal::query()
            ->with(['account:id,name,current_balance,color', 'person:id,name,color']);

        if ($person_id) {
            $query->where('person_id', $person_id);
        }

        return $query->orderBy('target_date', 'asc')
            ->orderBy('name', 'asc')
            ->get();
    }

    public function find(int $id): ?SavingsGoal
    {
        return SavingsGoal::query()
            ->with(['account:id,name,current_balance,color', 'person:id,name,color'])
            ->find($id);
    }

    public function create(array $data): SavingsGoal
    {
        return SavingsGoal::create($data);
    }

    public function update(SavingsGoal $goal, array $data): SavingsGoal
    {
        $goal->update($data);

        return $goal->fresh(['account:id,name,current_balance,color', 'person:id,name,color']);
    }

    public function delete(SavingsGoal $goal): void
    {
        $goal->delete();
    }
}
