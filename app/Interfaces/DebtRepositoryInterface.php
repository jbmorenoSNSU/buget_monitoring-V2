<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Debt;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Collection;

interface DebtRepositoryInterface
{
    public function paginate(?int $person_id = null): CursorPaginator;

    public function get_active(?int $person_id = null): Collection;

    public function all(): Collection;

    public function find(int $id): Debt;

    public function count_active(?int $person_id = null): int;

    public function create(array $data): Debt;

    public function update(Debt $debt, array $data): Debt;

    public function delete(Debt $debt): void;

    public function increment_principal(int $id, float $amount): void;

    public function decrement_principal(int $id, float $amount): void;
}
