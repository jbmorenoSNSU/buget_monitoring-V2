<?php

declare(strict_types=1);

namespace App\Actions\Person;

use App\DTOs\PersonDTO;
use App\Interfaces\PersonRepositoryInterface;
use App\Models\Person;

/**
 * Single-purpose action for updating a person profile.
 */
class UpdatePersonAction
{
    public function __construct(
        private PersonRepositoryInterface $personRepository
    ) {}

    /**
     * Execute the person update.
     */
    public function execute(Person $person, PersonDTO $dto): Person
    {
        return $this->personRepository->update($person, $dto->toArray());
    }
}
