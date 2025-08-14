<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    protected $fillable = [
        'assignment_id',
        'student_id',
        'graded_by',
        'points_earned',
        'points_possible',
        'percentage',
        'letter_grade',
        'feedback',
        'rubric_scores',
        'graded_at',
        'is_published',
    ];

    protected $casts = [
        'rubric_scores' => 'json',
        'graded_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($grade) {
            if ($grade->points_possible > 0) {
                $grade->percentage = round(($grade->points_earned / $grade->points_possible) * 100, 2);
                $grade->letter_grade = static::calculateLetterGrade($grade->percentage);
            }
        });
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(AssignmentSubmission::class, 'assignment_id', 'assignment_id')
                    ->where('student_id', $this->student_id ?? 0);
    }

    public static function calculateLetterGrade(float $percentage): string
    {
        if ($percentage >= 97) return 'A+';
        if ($percentage >= 93) return 'A';
        if ($percentage >= 90) return 'A-';
        if ($percentage >= 87) return 'B+';
        if ($percentage >= 83) return 'B';
        if ($percentage >= 80) return 'B-';
        if ($percentage >= 77) return 'C+';
        if ($percentage >= 73) return 'C';
        if ($percentage >= 70) return 'C-';
        if ($percentage >= 67) return 'D+';
        if ($percentage >= 63) return 'D';
        if ($percentage >= 60) return 'D-';
        return 'F';
    }

    public function getGradeColor(): string
    {
        return match (true) {
            $this->percentage >= 90 => 'text-green-600',
            $this->percentage >= 80 => 'text-blue-600',
            $this->percentage >= 70 => 'text-yellow-600',
            $this->percentage >= 60 => 'text-orange-600',
            default => 'text-red-600'
        };
    }

    public function isPassingGrade(): bool
    {
        return $this->percentage >= 60;
    }

    public function isExcellentGrade(): bool
    {
        return $this->percentage >= 90;
    }

    public function publish(): void
    {
        $this->update([
            'is_published' => true,
        ]);

        // Mark submission as graded
        $submission = $this->submission;
        if ($submission) {
            $submission->markAsGraded();
        }
    }

    public function unpublish(): void
    {
        $this->update([
            'is_published' => false,
        ]);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUnpublished($query)
    {
        return $query->where('is_published', false);
    }

    public function scopePassing($query)
    {
        return $query->where('percentage', '>=', 60);
    }

    public function scopeFailing($query)
    {
        return $query->where('percentage', '<', 60);
    }

    public function scopeExcellent($query)
    {
        return $query->where('percentage', '>=', 90);
    }

    public function scopeByLetterGrade($query, string $letterGrade)
    {
        return $query->where('letter_grade', $letterGrade);
    }
}
