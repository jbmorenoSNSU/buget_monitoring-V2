<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a file export record for report downloads.
 */
class Export extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'format',
        'file_name',
        'file_path',
        'status',
        'error',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    /**
     * Get the user who initiated this export.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
