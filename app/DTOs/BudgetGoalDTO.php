<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Immutable Data Transfer Object for creating or updating a BudgetGoal.
 */
final class BudgetGoalDTO
{
    /**
     * @param int $category_id
     * @param int $month
     * @param int $year
     * @param float $limit_amount
     */
    public function __construct(
        public readonly int $category_id,
        public readonly int $month,
        public readonly int $year,
        public readonly float $limit_amount,
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
            category_id: (int) $validated['category_id'],
            month: (int) $validated['month'],
            year: (int) $validated['year'],
            limit_amount: (float) $validated['limit_amount'],
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
            'category_id'  => $this->category_id,
            'month'        => $this->month,
            'year'         => $this->year,
            'limit_amount' => $this->limit_amount,
        ];
    }
}
