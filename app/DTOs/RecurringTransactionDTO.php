<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Immutable Data Transfer Object for creating or updating a RecurringTransaction.
 */
final class RecurringTransactionDTO
{
    public function __construct(
        public readonly int $account_id,
        public readonly int $category_id,
        public readonly string $type,
        public readonly float $amount,
        public readonly string $description,
        public readonly string $frequency,
        public readonly string $start_date,
        public readonly ?string $end_date,
        public readonly bool $is_active,
        public readonly ?int $debt_id = null,
    ) {}

    /**
     * Construct from a validated Form Request data array.
     *
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            account_id: (int) $validated['account_id'],
            category_id: (int) $validated['category_id'],
            type: (string) $validated['type'],
            amount: (float) $validated['amount'],
            description: (string) $validated['description'],
            frequency: (string) $validated['frequency'],
            start_date: (string) $validated['start_date'],
            end_date: $validated['end_date'] ?? null,
            is_active: (bool) ($validated['is_active'] ?? true),
            debt_id: isset($validated['debt_id']) ? (int) $validated['debt_id'] : null,
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
            'category_id' => $this->category_id,
            'type' => $this->type,
            'amount' => $this->amount,
            'description' => $this->description,
            'frequency' => $this->frequency,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_active' => $this->is_active,
            'debt_id' => $this->debt_id,
        ];
    }
}
