<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\RecurringTransaction;
use Illuminate\Database\Eloquent\Collection;

interface RecurringTransactionRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?RecurringTransaction;

    public function all_due(): Collection;

    public function upcoming(int $days): Collection;

    public function create(array $data): RecurringTransaction;

    public function update(RecurringTransaction $recurring, array $data): RecurringTransaction;

    public function delete(RecurringTransaction $recurring): void;
}
