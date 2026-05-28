<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\BudgetGoal;
use Illuminate\Database\Eloquent\Collection;

interface BudgetGoalRepositoryInterface
{
    public function for_month(int $month, int $year): Collection;

    public function find(int $id): ?BudgetGoal;

    public function create(array $data): BudgetGoal;

    public function update(BudgetGoal $goal, array $data): BudgetGoal;

    public function delete(BudgetGoal $goal): void;

    public function spent_by_category(int $category_id, int $month, int $year): float;
}
