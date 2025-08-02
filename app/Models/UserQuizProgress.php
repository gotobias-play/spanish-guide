<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserQuizProgress extends Model
{
    protected $fillable = [
        'user_id',
        'section_id',
        'score',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
