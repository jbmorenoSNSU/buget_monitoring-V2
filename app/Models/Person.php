<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a person/owner who can own financial accounts.
 */
class Person extends Model
{
    use SoftDeletes;

    /** @var string */
    protected $table = 'persons';

    /** @var array<int, string> */
    protected $fillable = [
        'name',
        'color',
        'is_active',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all accounts owned by this person.
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Get all transactions for this person's accounts.
     */
    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Transaction::class, Account::class);
    }

    /**
     * Scope: only active persons.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
