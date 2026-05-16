<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum representing category type classification.
 */
enum CategoryType: string
{
    case Income = 'income';
    case Expense = 'expense';
    case Both = 'both';

    /**
     * Get a human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Income => 'Income',
            self::Expense => 'Expense',
            self::Both => 'Both',
        };
    }

    /**
     * Get the badge color class.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::Income => 'bg-green-100 text-green-800',
            self::Expense => 'bg-red-100 text-red-800',
            self::Both => 'bg-blue-100 text-blue-800',
        };
    }
}
