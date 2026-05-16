<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BudgetGoal;
use App\Models\Transaction;

class BudgetGoalService
{
    public function getForMonth(int $month, int $year)
    {
        $goals = BudgetGoal::with('category')->forMonth($month, $year)->get();

        return $goals->map(function ($goal) use ($month, $year) {
            $spent = $this->getSpentAmount($goal->category_id, $month, $year);
            $limit = (float) $goal->limit_amount;
            $remaining = $limit - $spent;
            $percent = $limit > 0 ? round(($spent / $limit) * 100, 1) : 0;
            $status = $percent < 75 ? 'safe' : ($percent < 90 ? 'warning' : 'danger');

            $goal->spent = $spent;
            $goal->remaining = $remaining;
            $goal->percent = $percent;
            $goal->status = $status;

            return $goal;
        });
    }

    public function getSpentAmount(int $categoryId, int $month, int $year): float
    {
        return (float) Transaction::where('category_id', $categoryId)
            ->where('type', 'expense')
            ->forMonth($month, $year)
            ->sum('amount');
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

    public function hasWarnings(int $month, int $year): bool
    {
        $goals = $this->getForMonth($month, $year);
        return $goals->contains(fn ($g) => $g->percent >= 90);
    }
}
