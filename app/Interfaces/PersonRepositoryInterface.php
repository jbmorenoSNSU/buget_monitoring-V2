<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Person;
use Illuminate\Database\Eloquent\Collection;

interface PersonRepositoryInterface
{
    public function all(): Collection;

    public function all_active(): Collection;

    public function find(int $id): ?Person;

    public function create(array $data): Person;

    public function update(Person $person, array $data): Person;

    public function delete(Person $person): bool;

    public function has_accounts(Person $person): bool;
}
