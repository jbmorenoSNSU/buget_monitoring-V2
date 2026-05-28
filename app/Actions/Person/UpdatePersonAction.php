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
    /**
     * @param PersonRepositoryInterface $personRepository
     */
    public function __construct(
        private PersonRepositoryInterface $personRepository
    ) {}

    /**
     * Execute the person update.
     *
     * @param Person $person
     * @param PersonDTO $dto
     * @return Person
     */
    public function execute(Person $person, PersonDTO $dto): Person
    {
        return $this->personRepository->update($person, $dto->toArray());
    }
}
