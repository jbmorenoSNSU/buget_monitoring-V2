<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\BudgetGoalRepositoryInterface;
use App\Interfaces\TransactionRepositoryInterface;
use App\Models\BudgetGoal;
use Illuminate\Support\Collection;

/**
 * Service class handling business logic for monthly budget goals.
 */
class BudgetGoalService
{
    /**
     * Create a new BudgetGoalService instance.
     */
    public function __construct(
        private BudgetGoalRepositoryInterface $budgetGoalRepository,
        private TransactionRepositoryInterface $transactionRepository
    ) {}

    /**
     * Get monthly budget goals with calculated actual spending and status warnings.
     *
     * @return Collection<int, BudgetGoal>
     */
    public function get_for_month(int $month, int $year, ?int $person_id = null): Collection
    {
        $goals = $this->budgetGoalRepository->for_month($month, $year);
        $spent = $this->transactionRepository->spent_by_category_map($month, $year, $person_id);

        return $goals->map(function ($goal) use ($spent) {
            $spent_amount = (float) ($spent[$goal->category_id] ?? 0);
            $limit = (float) $goal->limit_amount;
            $remaining = $limit - $spent_amount;
            $percent = $limit > 0 ? round(($spent_amount / $limit) * 100, 1) : 0;
            $status = $percent < 75 ? 'safe' : ($percent < 90 ? 'warning' : 'danger');

            $goal->spent = $spent_amount;
            $goal->remaining = $remaining;
            $goal->percent = $percent;
            $goal->status = $status;

            return $goal;
        });
    }

    /**
     * Get actual spent amount for a specific category in a given month.
     */
    public function get_spent_amount(int $category_id, int $month, int $year): float
    {
        return $this->budgetGoalRepository->spent_by_category($category_id, $month, $year);
    }

    /**
     * Delete a budget goal.
     */
    public function delete(BudgetGoal $goal): void
    {
        $this->budgetGoalRepository->delete($goal);
    }

    /**
     * Check if any budget goal has warning status (90%+ spent).
     */
    public function has_warnings(int $month, int $year): bool
    {
        $goals = $this->get_for_month($month, $year);

        return $goals->contains(fn ($g) => $g->percent >= 90);
    }
}
