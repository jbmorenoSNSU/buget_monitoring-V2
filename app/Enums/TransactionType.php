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

    /**
     * Get the CSS color class for the transaction type.
     */
    public function color(): string
    {
        return match ($this) {
            self::Income => 'text-green-600',
            self::Expense => 'text-red-600',
            self::Transfer => 'text-purple-600',
        };
    }

    /**
     * Get the background color class for badges.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::Income => 'bg-green-100 text-green-800',
            self::Expense => 'bg-red-100 text-red-800',
            self::Transfer => 'bg-purple-100 text-purple-800',
        };
    }
}
