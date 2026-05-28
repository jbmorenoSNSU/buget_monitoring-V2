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
}
