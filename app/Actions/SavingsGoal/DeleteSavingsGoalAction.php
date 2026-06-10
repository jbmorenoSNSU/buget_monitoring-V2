<?php

declare(strict_types=1);

namespace App\Actions\SavingsGoal;

use App\Interfaces\SavingsGoalRepositoryInterface;
use App\Models\SavingsGoal;

/**
 * Single-purpose action for deleting a savings goal.
 */
class DeleteSavingsGoalAction
{
    public function __construct(
        private SavingsGoalRepositoryInterface $savingsGoalRepository
    ) {}

    /**
     * Execute the savings goal deletion.
     */
    public function execute(SavingsGoal $goal): void
    {
        $this->savingsGoalRepository->delete($goal);
    }
}
