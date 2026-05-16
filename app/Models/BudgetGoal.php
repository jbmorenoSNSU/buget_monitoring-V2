<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a monthly budget goal/limit for a specific category.
 */
class BudgetGoal extends Model
{
    /** @var array<int, string> */
    protected $fillable = [
        'category_id',
        'month',
        'year',
        'limit_amount',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'limit_amount' => 'decimal:2',
    ];

    /**
     * Get the category for this budget goal.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope: filter by specific month and year.
     */
    public function scopeForMonth(Builder $query, int $month, int $year): Builder
    {
        return $query->where('month', $month)->where('year', $year);
    }
}
