<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a financial account (bank, cash, e-wallet, etc.).
 */
class Account extends Model
{
    use HasFactory, SoftDeletes;

    /** @var array<int, string> */
    protected $fillable = [
        'account_type_id',
        'person_id',
        'name',
        'description',
        'initial_balance',
        'current_balance',
        'color',
        'is_active',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the account type for this account.
     */
    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class, 'account_type_id');
    }

    /**
     * Get the person/owner of this account.
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    /**
     * Get all transactions for this account.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'account_id', 'id');
    }

    /**
     * Get all incoming transfers to this account.
     */
    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(Transaction::class, 'transfer_to_account_id');
    }

    /**
     * Scope: only active accounts.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: filter by account type.
     */
    public function scopeByType(Builder $query, int $typeId): Builder
    {
        return $query->where('account_type_id', $typeId);
    }
}
