<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

/**
 * Class TransactionPolicy
 *
 * Handles authorization checks for Transaction model access.
 */
class TransactionPolicy
{
    /**
     * Determine if any transactions can be viewed.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine if a specific transaction can be viewed.
     */
    public function view(?User $user, Transaction $transaction): bool
    {
        return true;
    }

    /**
     * Determine if a transaction can be created.
     */
    public function create(?User $user): bool
    {
        return true;
    }

    /**
     * Determine if a transaction can be updated.
     */
    public function update(?User $user, Transaction $transaction): bool
    {
        return true;
    }

    /**
     * Determine if a transaction can be deleted.
     */
    public function delete(?User $user, Transaction $transaction): bool
    {
        return true;
    }
}
