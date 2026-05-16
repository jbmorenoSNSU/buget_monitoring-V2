<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CategoryType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a transaction category (Food, Salary, Transport, etc.).
 */
class Category extends Model
{
    use SoftDeletes;

    /** @var array<int, string> */
    protected $fillable = [
        'name',
        'type',
        'icon',
        'color',
        'is_active',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'type' => CategoryType::class,
        'is_active' => 'boolean',
    ];

    /**
     * Get all transactions in this category.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all budget goals for this category.
     */
    public function budgetGoals(): HasMany
    {
        return $this->hasMany(BudgetGoal::class);
    }

    /**
     * Get all recurring transactions for this category.
     */
    public function recurringTransactions(): HasMany
    {
        return $this->hasMany(RecurringTransaction::class);
    }

    /**
     * Scope: only active categories.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: filter by category type.
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type)->orWhere('type', 'both');
    }

    /**
     * Scope: income categories only.
     */
    public function scopeIncome(Builder $query): Builder
    {
        return $query->whereIn('type', ['income', 'both']);
    }

    /**
     * Scope: expense categories only.
     */
    public function scopeExpense(Builder $query): Builder
    {
        return $query->whereIn('type', ['expense', 'both']);
    }
}
