<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Debt extends Model
{
    use SoftDeletes;

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
        'principal_amount' => 'float',
        'interest_rate' => 'float',
        'minimum_payment' => 'float',
        'due_date_day' => 'integer',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
