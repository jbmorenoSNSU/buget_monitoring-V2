<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Immutable Data Transfer Object for creating or updating an Account.
 */
final class AccountDTO
{
    /**
     * @param int $account_type_id
     * @param string $name
     * @param int|null $person_id
     * @param string|null $description
     * @param float $initial_balance
     * @param bool $is_active
     */
    public function __construct(
        public readonly int $account_type_id,
        public readonly string $name,
        public readonly ?int $person_id,
        public readonly ?string $description,
        public readonly float $initial_balance,
        public readonly bool $is_active,
    ) {}

    /**
     * Construct from a validated Form Request data array.
     *
     * @param array<string, mixed> $validated
     * @return self
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            account_type_id: (int) $validated['account_type_id'],
            name: (string) $validated['name'],
            person_id: isset($validated['person_id']) ? (int) $validated['person_id'] : null,
            description: $validated['description'] ?? null,
            initial_balance: (float) ($validated['initial_balance'] ?? 0),
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
            'account_type_id' => $this->account_type_id,
            'name'            => $this->name,
            'person_id'       => $this->person_id,
            'description'     => $this->description,
            'initial_balance' => $this->initial_balance,
            'is_active'       => $this->is_active,
        ];
    }
}
