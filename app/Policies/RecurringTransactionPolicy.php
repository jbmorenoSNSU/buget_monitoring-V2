<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RecurringTransaction;
use App\Models\User;

/**
 * Class RecurringTransactionPolicy
 *
 * Handles authorization checks for RecurringTransaction model access.
 */
class RecurringTransactionPolicy
{
    /**
     * Determine if any recurring transactions can be viewed.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine if a specific recurring transaction can be viewed.
     */
    public function view(?User $user, RecurringTransaction $recurringTransaction): bool
    {
        return true;
    }

    /**
     * Determine if a recurring transaction can be created.
     */
    public function create(?User $user): bool
    {
        return true;
    }

    /**
     * Determine if a recurring transaction can be updated.
     */
    public function update(?User $user, RecurringTransaction $recurringTransaction): bool
    {
        return true;
    }

    /**
     * Determine if a recurring transaction can be deleted.
     */
    public function delete(?User $user, RecurringTransaction $recurringTransaction): bool
    {
        return true;
    }
}
