<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a savings goal (e.g. emergency fund, travel fund).
 */
class SavingsGoal extends Model
{
    use HasFactory;

    /** @var array<int, string> */
    protected $fillable = [
        'account_id',
        'person_id',
        'name',
        'target_amount',
        'current_amount',
        'target_date',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'account_id' => 'integer',
        'person_id' => 'integer',
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'target_date' => 'date',
    ];

    /**
     * Get the account linked to this savings goal.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Get the person (owner) associated with this savings goal.
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }
}
