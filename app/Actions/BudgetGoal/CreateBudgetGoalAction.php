<?php

declare(strict_types=1);

namespace App\Actions\BudgetGoal;

use App\DTOs\BudgetGoalDTO;
use App\Interfaces\BudgetGoalRepositoryInterface;
use App\Models\BudgetGoal;

/**
 * Single-purpose action for creating a budget goal.
 */
class CreateBudgetGoalAction
{
    /**
     * @param BudgetGoalRepositoryInterface $budgetGoalRepository
     */
    public function __construct(
        private BudgetGoalRepositoryInterface $budgetGoalRepository
    ) {}

    /**
     * Execute the budget goal creation.
     *
     * @param BudgetGoalDTO $dto
     * @return BudgetGoal
     */
    public function execute(BudgetGoalDTO $dto): BudgetGoal
    {
        return $this->budgetGoalRepository->create($dto->toArray());
    }
}
