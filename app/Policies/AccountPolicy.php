<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

/**
 * Class AccountPolicy
 *
 * Handles authorization checks for Account model access.
 */
class AccountPolicy
{
    /**
     * Determine if any accounts can be viewed.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine if a specific account can be viewed.
     */
    public function view(?User $user, Account $account): bool
    {
        return true;
    }

    /**
     * Determine if an account can be created.
     */
    public function create(?User $user): bool
    {
        return true;
    }

    /**
     * Determine if an account can be updated.
     */
    public function update(?User $user, Account $account): bool
    {
        return true;
    }

    /**
     * Determine if an account can be deleted.
     */
    public function delete(?User $user, Account $account): bool
    {
        return true;
    }
}
