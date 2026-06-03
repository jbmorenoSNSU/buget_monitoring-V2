<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\PersonRepositoryInterface;
use App\Models\Person;
use Illuminate\Database\Eloquent\Collection;

class PersonService
{
    public function __construct(
        private PersonRepositoryInterface $personRepository
    ) {}

    /**
     * Get all persons with aggregated account data.
     */
    public function get_all(): Collection
    {
        return $this->personRepository->all();
    }

    /**
     * Get only active persons (for dropdowns).
     */
    public function get_active(): Collection
    {
        return $this->personRepository->all_active();
    }

    public function can_delete(Person $person): bool
    {
        return ! $this->personRepository->has_accounts($person);
    }

    public function delete(Person $person): bool
    {
        if (! $this->can_delete($person)) {
            return false;
        }

        return $this->personRepository->delete($person);
    }
}
