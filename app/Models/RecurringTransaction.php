<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RecurringFrequency;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a recurring transaction template for automated generation.
 */
class RecurringTransaction extends Model
{
    use SoftDeletes;

    /** @var array<int, string> */
    protected $fillable = [
        'account_id',
        'category_id',
        'type',
        'amount',
        'description',
        'frequency',
        'start_date',
        'end_date',
        'next_due_date',
        'last_generated_date',
        'is_active',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'type' => TransactionType::class,
        'frequency' => RecurringFrequency::class,
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_due_date' => 'date',
        'last_generated_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the account for this recurring transaction.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Get the category for this recurring transaction.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get all generated transactions from this recurring template.
     */
    public function generatedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'recurring_id');
    }

    /**
     * Scope: only active recurring transactions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: recurring transactions that are due (next_due_date <= today).
     */
    public function scopeDue(Builder $query): Builder
    {
        return $query->where('next_due_date', '<=', now()->toDateString());
    }
}
