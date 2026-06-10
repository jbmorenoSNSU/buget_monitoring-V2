<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Export extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'format',
        'file_name',
        'file_path',
        'status',
        'error',
    ];
}
