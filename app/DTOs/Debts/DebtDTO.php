<?php

declare(strict_types=1);

namespace App\DTOs\Debts;

class DebtDTO
{
    public function __construct(
        public readonly ?int $person_id,
        public readonly string $name,
        public readonly float $principal_amount,
        public readonly float $interest_rate,
        public readonly float $minimum_payment,
        public readonly ?int $due_date_day,
        public readonly string $status
    ) {}

    public static function fromRequest(array $validated): self
    {
        return new self(
            person_id: isset($validated['person_id']) ? (int) $validated['person_id'] : null,
            name: $validated['name'],
            principal_amount: (float) $validated['principal_amount'],
            interest_rate: (float) ($validated['interest_rate'] ?? 0),
            minimum_payment: (float) $validated['minimum_payment'],
            due_date_day: isset($validated['due_date_day']) ? (int) $validated['due_date_day'] : null,
            status: $validated['status'] ?? 'active'
        );
    }
}
