<?php

declare(strict_types=1);

namespace App\Actions\SavingsGoal;

use App\DTOs\SavingsGoal\SavingsGoalDTO;
use App\Interfaces\SavingsGoalRepositoryInterface;
use App\Models\SavingsGoal;

/**
 * Single-purpose action for creating a savings goal.
 */
class CreateSavingsGoalAction
{
    public function __construct(
        private SavingsGoalRepositoryInterface $savingsGoalRepository
    ) {}

    /**
     * Execute the savings goal creation.
     */
    public function execute(SavingsGoalDTO $dto): SavingsGoal
    {
        return $this->savingsGoalRepository->create($dto->toArray());
    }
}
