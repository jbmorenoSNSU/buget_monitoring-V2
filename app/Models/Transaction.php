<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a single financial transaction (income, expense, or transfer).
 */
class Transaction extends Model
{
    use HasFactory, SoftDeletes;

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
        'split_with_person_id',
        'split_amount',
        'debt_id',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'account_id' => 'integer',
        'category_id' => 'integer',
        'transfer_to_account_id' => 'integer',
        'recurring_id' => 'integer',
        'split_with_person_id' => 'integer',
        'type' => TransactionType::class,
        'amount' => 'decimal:2',
        'split_amount' => 'decimal:2',
        'transaction_date' => 'date',
        'debt_id' => 'integer',
    ];

    /**
     * Get the account this transaction belongs to.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Get the category for this transaction.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
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
     * Get the person this transaction is split with.
     */
    public function splitWithPerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'split_with_person_id');
    }

    /**
     * Get the debt this transaction is linked to.
     */
    public function debt(): BelongsTo
    {
        return $this->belongsTo(Debt::class, 'debt_id');
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
    public function scopeByAccount(Builder $query, int $account_id): Builder
    {
        return $query->where('account_id', $account_id);
    }

    /**
     * Scope: filter by category.
     */
    public function scopeByCategory(Builder $query, int $category_id): Builder
    {
        return $query->where('category_id', $category_id);
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
