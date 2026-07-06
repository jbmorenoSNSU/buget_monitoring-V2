<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum representing possible debt statuses.
 */
enum DebtStatus: string
{
    case Active = 'active';
    case Paid = 'paid';

    /**
     * Get a human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Paid => 'Paid',
        };
    }
}
