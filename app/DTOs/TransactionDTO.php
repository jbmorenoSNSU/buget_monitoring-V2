<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Immutable Data Transfer Object for creating or updating a Transaction.
 */
final class TransactionDTO
{
    /**
     * @param int $account_id
     * @param string $type
     * @param float $amount
     * @param string $transaction_date
     * @param string $description
     * @param int|null $category_id
     * @param string|null $notes
     * @param string|null $reference_number
     * @param int|null $transfer_to_account_id
     */
    public function __construct(
        public readonly int $account_id,
        public readonly string $type,
        public readonly float $amount,
        public readonly string $transaction_date,
        public readonly string $description,
        public readonly ?int $category_id,
        public readonly ?string $notes,
        public readonly ?string $reference_number,
        public readonly ?int $transfer_to_account_id,
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
            account_id: (int) $validated['account_id'],
            type: (string) $validated['type'],
            amount: (float) $validated['amount'],
            transaction_date: (string) $validated['transaction_date'],
            description: (string) $validated['description'],
            category_id: isset($validated['category_id']) ? (int) $validated['category_id'] : null,
            notes: $validated['notes'] ?? null,
            reference_number: $validated['reference_number'] ?? null,
            transfer_to_account_id: isset($validated['transfer_to_account_id'])
                ? (int) $validated['transfer_to_account_id']
                : null,
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
            'account_id'             => $this->account_id,
            'type'                   => $this->type,
            'amount'                 => $this->amount,
            'transaction_date'       => $this->transaction_date,
            'description'            => $this->description,
            'category_id'            => $this->category_id,
            'notes'                  => $this->notes,
            'reference_number'       => $this->reference_number,
            'transfer_to_account_id' => $this->transfer_to_account_id,
        ];
    }
}
