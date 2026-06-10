<?php

declare(strict_types=1);

namespace App\DTOs\SavingsGoal;

class SavingsGoalDTO
{
    public function __construct(
        public readonly ?int $account_id,
        public readonly ?int $person_id,
        public readonly string $name,
        public readonly float $target_amount,
        public readonly float $current_amount,
        public readonly ?string $target_date
    ) {}

    public static function fromRequest(array $validated): self
    {
        return new self(
            account_id: isset($validated['account_id']) && $validated['account_id'] !== '' ? (int) $validated['account_id'] : null,
            person_id: isset($validated['person_id']) && $validated['person_id'] !== '' ? (int) $validated['person_id'] : null,
            name: $validated['name'],
            target_amount: (float) $validated['target_amount'],
            current_amount: (float) ($validated['current_amount'] ?? 0.0),
            target_date: ! empty($validated['target_date']) ? $validated['target_date'] : null
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
            'account_id' => $this->account_id,
            'person_id' => $this->person_id,
            'name' => $this->name,
            'target_amount' => $this->target_amount,
            'current_amount' => $this->current_amount,
            'target_date' => $this->target_date,
        ];
    }
}
