<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a monthly budget goal/limit for a specific category.
 */
class BudgetGoal extends Model
{
    use HasFactory, SoftDeletes;

    /** @var array<int, string> */
    protected $fillable = [
        'category_id',
        'person_id',
        'month',
        'year',
        'limit_amount',
        'is_rollover_enabled',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'category_id' => 'integer',
        'month' => 'integer',
        'year' => 'integer',
        'limit_amount' => 'decimal:2',
        'is_rollover_enabled' => 'boolean',
    ];

    /**
     * Get the category for this budget goal.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the person for this budget goal.
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    /**
     * Scope: filter by specific month and year.
     */
    public function scopeForMonth(Builder $query, int $month, int $year): Builder
    {
        return $query->where('month', $month)->where('year', $year);
    }
}
