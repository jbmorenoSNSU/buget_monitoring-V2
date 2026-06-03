<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Immutable Data Transfer Object for creating or updating a Person.
 */
final class PersonDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $color,
    ) {}

    /**
     * Construct from a validated Form Request data array.
     *
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            name: (string) $validated['name'],
            color: (string) $validated['color'],
        );
    }

    /**
     * Return as a plain array suitable for Eloquent mass assignment.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'color' => $this->color,
        ];
    }
}
