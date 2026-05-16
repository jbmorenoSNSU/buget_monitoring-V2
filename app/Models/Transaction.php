<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a single financial transaction (income, expense, or transfer).
 */
class Transaction extends Model
{
    use SoftDeletes;

    /** @var array<int, string> */
    protected $fillable = [
        'account_id',
        'category_id',
        'type',
        'amount',
        'transaction_date',
        'description',
        'notes',
        'reference_number',
        'transfer_to_account_id',
        'recurring_id',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'type' => TransactionType::class,
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    /**
     * Get the account this transaction belongs to.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the category for this transaction.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the destination account for transfer transactions.
     */
    public function transferToAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'transfer_to_account_id');
    }

    /**
     * Get the recurring transaction that generated this transaction.
     */
    public function recurringTransaction(): BelongsTo
    {
        return $this->belongsTo(RecurringTransaction::class, 'recurring_id');
    }

    /**
     * Scope: filter by transaction type.
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: filter by account.
     */
    public function scopeByAccount(Builder $query, int $accountId): Builder
    {
        return $query->where('account_id', $accountId);
    }

    /**
     * Scope: filter by category.
     */
    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope: filter by date range.
     */
    public function scopeByDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('transaction_date', [$from, $to]);
    }

    /**
     * Scope: current month transactions.
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('transaction_date', now()->month)
                     ->whereYear('transaction_date', now()->year);
    }

    /**
     * Scope: for a specific month and year.
     */
    public function scopeForMonth(Builder $query, int $month, int $year): Builder
    {
        return $query->whereMonth('transaction_date', $month)
                     ->whereYear('transaction_date', $year);
    }
}
