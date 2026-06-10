<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Immutable Data Transfer Object for creating or updating a BudgetGoal.
 */
final class BudgetGoalDTO
{
    public function __construct(
        public readonly int $category_id,
        public readonly ?int $person_id,
        public readonly int $month,
        public readonly int $year,
        public readonly float $limit_amount,
        public readonly bool $is_rollover_enabled = true,
    ) {}

    /**
     * Construct from a validated Form Request data array.
     *
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            category_id: (int) $validated['category_id'],
            person_id: isset($validated['person_id']) ? (int) $validated['person_id'] : null,
            month: (int) $validated['month'],
            year: (int) $validated['year'],
            limit_amount: (float) $validated['limit_amount'],
            is_rollover_enabled: isset($validated['is_rollover_enabled']) ? (bool) $validated['is_rollover_enabled'] : true,
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
            'category_id' => $this->category_id,
            'person_id' => $this->person_id,
            'month' => $this->month,
            'year' => $this->year,
            'limit_amount' => $this->limit_amount,
            'is_rollover_enabled' => $this->is_rollover_enabled,
        ];
    }
}
