<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Account;
use Illuminate\Database\Eloquent\Collection;

interface AccountRepositoryInterface
{
    public function all(): Collection;

    public function all_active(?int $person_id = null): Collection;

    public function find(int $id): ?Account;

    public function create(array $data): Account;

    public function update(Account $account, array $data): Account;

    public function delete(Account $account): bool;

    public function total_balance(?int $person_id = null): float;

    public function has_transactions(Account $account): bool;
}
