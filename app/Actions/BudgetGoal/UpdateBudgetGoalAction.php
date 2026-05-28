<?php

declare(strict_types=1);

namespace App\Actions\BudgetGoal;

use App\DTOs\BudgetGoalDTO;
use App\Interfaces\BudgetGoalRepositoryInterface;
use App\Models\BudgetGoal;

/**
 * Single-purpose action for updating a budget goal.
 */
class UpdateBudgetGoalAction
{
    /**
     * @param BudgetGoalRepositoryInterface $budgetGoalRepository
     */
    public function __construct(
        private BudgetGoalRepositoryInterface $budgetGoalRepository
    ) {}

    /**
     * Execute the budget goal update.
     *
     * @param BudgetGoal $budgetGoal
     * @param BudgetGoalDTO $dto
     * @return BudgetGoal
     */
    public function execute(BudgetGoal $budgetGoal, BudgetGoalDTO $dto): BudgetGoal
    {
        return $this->budgetGoalRepository->update($budgetGoal, $dto->toArray());
    }
}
