<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Debt;
use App\Models\User;

/**
 * Handles authorization checks for Debt model access.
 */
class DebtPolicy
{
    /**
     * Determine if any debts can be viewed.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine if a specific debt can be viewed.
     */
    public function view(?User $user, Debt $debt): bool
    {
        return true;
    }

    /**
     * Determine if a debt can be created.
     */
    public function create(?User $user): bool
    {
        return true;
    }

    /**
     * Determine if a debt can be updated.
     */
    public function update(?User $user, Debt $debt): bool
    {
        return true;
    }

    /**
     * Determine if a debt can be deleted.
     */
    public function delete(?User $user, Debt $debt): bool
    {
        return true;
    }
}
