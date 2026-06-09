<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Debt;
use Illuminate\Pagination\LengthAwarePaginator;

interface DebtRepositoryInterface
{
    public function paginate(?int $person_id = null): LengthAwarePaginator;

    public function create(array $data): Debt;

    public function update(Debt $debt, array $data): Debt;

    public function delete(Debt $debt): void;
}
