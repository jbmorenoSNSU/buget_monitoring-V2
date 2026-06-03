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
    public function __construct(
        private PersonRepositoryInterface $personRepository
    ) {}

    /**
     * Execute the person creation.
     */
    public function execute(PersonDTO $dto): Person
    {
        return $this->personRepository->create($dto->toArray());
    }
}
