<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BudgetGoal;
use App\Models\User;

/**
 * Class BudgetGoalPolicy
 *
 * Handles authorization checks for BudgetGoal model access.
 */
class BudgetGoalPolicy
{
    /**
     * Determine if any budget goals can be viewed.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine if a specific budget goal can be viewed.
     */
    public function view(?User $user, BudgetGoal $budgetGoal): bool
    {
        return true;
    }

    /**
     * Determine if a budget goal can be created.
     */
    public function create(?User $user): bool
    {
        return true;
    }

    /**
     * Determine if a budget goal can be updated.
     */
    public function update(?User $user, BudgetGoal $budgetGoal): bool
    {
        return true;
    }

    /**
     * Determine if a budget goal can be deleted.
     */
    public function delete(?User $user, BudgetGoal $budgetGoal): bool
    {
        return true;
    }
}
