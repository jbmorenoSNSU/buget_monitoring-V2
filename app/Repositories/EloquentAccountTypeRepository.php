<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\AccountTypeRepositoryInterface;
use App\Models\AccountType;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class EloquentAccountTypeRepository
 *
 * Implements the AccountTypeRepositoryInterface using Eloquent queries.
 */
class EloquentAccountTypeRepository implements AccountTypeRepositoryInterface
{
    /**
     * Retrieve all account types with explicit column selection.
     *
     * @return Collection<int, AccountType>
     */
    public function all(): Collection
    {
        return AccountType::select(['id', 'name', 'icon'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Find a specific account type by ID.
     *
     * @param int $id
     * @return AccountType|null
     */
    public function find(int $id): ?AccountType
    {
        return AccountType::select(['id', 'name', 'icon'])->find($id);
    }
}
