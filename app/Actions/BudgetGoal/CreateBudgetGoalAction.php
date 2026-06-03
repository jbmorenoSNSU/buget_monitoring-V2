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
    public function __construct(
        private BudgetGoalRepositoryInterface $budgetGoalRepository
    ) {}

    /**
     * Execute the budget goal creation.
     */
    public function execute(BudgetGoalDTO $dto): BudgetGoal
    {
        return $this->budgetGoalRepository->create($dto->toArray());
    }
}
