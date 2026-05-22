<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BudgetGoal;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class BudgetGoalService
{
    public function getForMonth(int $month, int $year, ?int $personId = null)
    {
        $goals = BudgetGoal::with('category:id,name,icon')->forMonth($month, $year)->get();

        $spentQuery = Transaction::where('type', 'expense')
            ->forMonth($month, $year)
            ->selectRaw('category_id, SUM(amount) as spent')
            ->groupBy('category_id');

        if ($personId) {
            $spentQuery->whereHas('account', fn ($q) => $q->where('person_id', $personId));
        }

        $spent = $spentQuery->pluck('spent', 'category_id');

        return $goals->map(function ($goal) use ($spent) {
            $spentAmount = (float) ($spent->get($goal->category_id) ?? 0);
            $limit = (float) $goal->limit_amount;
            $remaining = $limit - $spentAmount;
            $percent = $limit > 0 ? round(($spentAmount / $limit) * 100, 1) : 0;
            $status = $percent < 75 ? 'safe' : ($percent < 90 ? 'warning' : 'danger');

            $goal->spent = $spentAmount;
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
