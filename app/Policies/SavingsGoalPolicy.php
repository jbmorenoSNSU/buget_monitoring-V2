<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SavingsGoal;
use App\Models\User;

/**
 * Handles authorization checks for SavingsGoal model access.
 */
class SavingsGoalPolicy
{
    /**
     * Determine if any savings goals can be viewed.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine if a specific savings goal can be viewed.
     */
    public function view(?User $user, SavingsGoal $savingsGoal): bool
    {
        return true;
    }

    /**
     * Determine if a savings goal can be created.
     */
    public function create(?User $user): bool
    {
        return true;
    }

    /**
     * Determine if a savings goal can be updated.
     */
    public function update(?User $user, SavingsGoal $savingsGoal): bool
    {
        return true;
    }

    /**
     * Determine if a savings goal can be deleted.
     */
    public function delete(?User $user, SavingsGoal $savingsGoal): bool
    {
        return true;
    }
}
