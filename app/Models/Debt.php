<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a tracked debt or liability (e.g. credit card, loan).
 */
class Debt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'person_id',
        'name',
        'principal_amount',
        'interest_rate',
        'minimum_payment',
        'due_date_day',
        'status',
    ];

    protected $casts = [
        'person_id' => 'integer',
        'principal_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'minimum_payment' => 'decimal:2',
        'due_date_day' => 'integer',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'debt_id');
    }
}
