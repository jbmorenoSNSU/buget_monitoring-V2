<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a type of financial account (Cash, Bank, E-Wallet, etc.).
 */
class AccountType extends Model
{
    /** @var array<int, string> */
    protected $fillable = [
        'name',
        'icon',
    ];

    /**
     * Get all accounts of this type.
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }
}
