<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a person/owner who can own financial accounts.
 */
class Person extends Model
{
    use HasFactory, SoftDeletes;

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
        return $this->hasMany(Account::class, 'person_id', 'id');
    }

    /**
     * Get all transactions for this person's accounts.
     */
    public function transactions(): HasManyThrough
    {
        return $this->hasManyThrough(Transaction::class, Account::class, 'person_id', 'account_id', 'id', 'id');
    }

    /**
     * Scope: only active persons.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
