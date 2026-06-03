<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Immutable Data Transfer Object for creating or updating a Category.
 */
final class CategoryDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly string $icon,
        public readonly string $color,
        public readonly bool $is_active,
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
            type: (string) $validated['type'],
            icon: (string) $validated['icon'],
            color: (string) $validated['color'],
            is_active: (bool) ($validated['is_active'] ?? true),
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
            'type' => $this->type,
            'icon' => $this->icon,
            'color' => $this->color,
            'is_active' => $this->is_active,
        ];
    }
}
