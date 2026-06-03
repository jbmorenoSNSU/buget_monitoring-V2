<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\BudgetGoalRepositoryInterface;
use App\Models\BudgetGoal;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

class EloquentBudgetGoalRepository implements BudgetGoalRepositoryInterface
{
    public function for_month(int $month, int $year): Collection
    {
        return BudgetGoal::with('category:id,name,icon,color')
            ->forMonth($month, $year)
            ->get();
    }

    public function find(int $id): ?BudgetGoal
    {
        return BudgetGoal::with('category:id,name,icon,color')->find($id);
    }

    public function create(array $data): BudgetGoal
    {
        return BudgetGoal::create($data);
    }

    public function update(BudgetGoal $goal, array $data): BudgetGoal
    {
        $goal->update($data);

        return $goal->fresh();
    }

    public function delete(BudgetGoal $goal): void
    {
        $goal->delete();
    }

    public function spent_by_category(int $category_id, int $month, int $year): float
    {
        return (float) Transaction::where('category_id', $category_id)
            ->where('type', 'expense')
            ->forMonth($month, $year)
            ->sum('amount');
    }
}
