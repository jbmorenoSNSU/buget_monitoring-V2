<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum representing possible transaction types in the system.
 */
enum TransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';
    case Transfer = 'transfer';

    /**
     * Get a human-readable label for the transaction type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Income => 'Income',
            self::Expense => 'Expense',
            self::Transfer => 'Transfer',
        };
    }
}
