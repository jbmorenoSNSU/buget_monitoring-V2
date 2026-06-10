<?php

declare(strict_types=1);

namespace App\Actions\SavingsGoal;

use App\DTOs\SavingsGoal\SavingsGoalDTO;
use App\Interfaces\SavingsGoalRepositoryInterface;
use App\Models\SavingsGoal;

/**
 * Single-purpose action for updating an existing savings goal.
 */
class UpdateSavingsGoalAction
{
    public function __construct(
        private SavingsGoalRepositoryInterface $savingsGoalRepository
    ) {}

    /**
     * Execute the savings goal update.
     */
    public function execute(SavingsGoal $goal, SavingsGoalDTO $dto): SavingsGoal
    {
        return $this->savingsGoalRepository->update($goal, $dto->toArray());
    }
}
