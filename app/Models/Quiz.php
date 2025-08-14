<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'title',
        'is_timed',
        'time_limit_seconds',
        'time_per_question_seconds',
        'show_timer',
        'timer_settings',
    ];

    protected $casts = [
        'is_timed' => 'boolean',
        'show_timer' => 'boolean',
        'timer_settings' => 'json',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}