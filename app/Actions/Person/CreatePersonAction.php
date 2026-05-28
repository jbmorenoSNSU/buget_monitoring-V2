<?php

declare(strict_types=1);

namespace App\Actions\Person;

use App\DTOs\PersonDTO;
use App\Interfaces\PersonRepositoryInterface;
use App\Models\Person;

/**
 * Single-purpose action for creating a person profile.
 */
class CreatePersonAction
{
    /**
     * @param PersonRepositoryInterface $personRepository
     */
    public function __construct(
        private PersonRepositoryInterface $personRepository
    ) {}

    /**
     * Execute the person creation.
     *
     * @param PersonDTO $dto
     * @return Person
     */
    public function execute(PersonDTO $dto): Person
    {
        return $this->personRepository->create($dto->toArray());
    }
}
