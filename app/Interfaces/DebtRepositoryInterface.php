<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Debt;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Collection;

interface DebtRepositoryInterface
{
    public function paginate(?int $person_id = null): CursorPaginator;

    public function all(): Collection;

    public function create(array $data): Debt;

    public function update(Debt $debt, array $data): Debt;

    public function delete(Debt $debt): void;
}
