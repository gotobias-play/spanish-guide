<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'certificate_code',
        'course_title',
        'user_name',
        'total_quizzes',
        'completed_quizzes',
        'average_score',
        'total_points_earned',
        'course_started_at',
        'course_completed_at',
        'quiz_results',
        'certificate_data',
    ];

    protected $casts = [
        'quiz_results' => 'array',
        'certificate_data' => 'array',
        'course_started_at' => 'datetime',
        'course_completed_at' => 'datetime',
        'average_score' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function generateCertificateCode(): string
    {
        $courseCode = strtoupper(substr($this->course_title, 0, 3));
        $year = now()->format('Y');
        $userIdPadded = str_pad($this->user_id, 3, '0', STR_PAD_LEFT);
        
        return "CERT-{$courseCode}-{$userIdPadded}-{$year}";
    }

    public function getCompletionPercentageAttribute(): float
    {
        if ($this->total_quizzes === 0) return 0;
        return round(($this->completed_quizzes / $this->total_quizzes) * 100, 2);
    }

    public function getGradeLevelAttribute(): string
    {
        $score = $this->average_score;
        
        if ($score >= 95) return 'Excelente';
        if ($score >= 85) return 'Muy Bueno';
        if ($score >= 75) return 'Bueno';
        if ($score >= 60) return 'Satisfactorio';
        
        return 'En Progreso';
    }
}
