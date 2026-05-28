<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\AccountType;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface AccountTypeRepositoryInterface
 *
 * Defines the contract for data access operations related to AccountType models.
 */
interface AccountTypeRepositoryInterface
{
    /**
     * Retrieve all account types.
     *
     * @return Collection<int, AccountType>
     */
    public function all(): Collection;

    /**
     * Find a specific account type by ID.
     *
     * @param int $id
     * @return AccountType|null
     */
    public function find(int $id): ?AccountType;
}
