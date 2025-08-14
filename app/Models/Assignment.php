<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    protected $fillable = [
        'class_id',
        'instructor_id',
        'title',
        'description',
        'type',
        'content',
        'max_points',
        'assigned_at',
        'due_date',
        'allow_late_submission',
        'late_penalty_percent',
        'is_published',
        'settings',
    ];

    protected $casts = [
        'content' => 'json',
        'assigned_at' => 'datetime',
        'due_date' => 'datetime',
        'allow_late_submission' => 'boolean',
        'is_published' => 'boolean',
        'settings' => 'json',
    ];

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class, 'assignment_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'assignment_id');
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast();
    }

    public function isDue(): bool
    {
        return $this->due_date && $this->due_date->isToday();
    }

    public function isUpcoming(): bool
    {
        return $this->due_date && $this->due_date->isFuture();
    }

    public function canSubmit(): bool
    {
        if (!$this->is_published) {
            return false;
        }

        if ($this->isOverdue() && !$this->allow_late_submission) {
            return false;
        }

        return true;
    }

    public function getSubmissionForStudent(int $studentId): ?AssignmentSubmission
    {
        return $this->submissions()->where('student_id', $studentId)->first();
    }

    public function getGradeForStudent(int $studentId): ?Grade
    {
        return $this->grades()->where('student_id', $studentId)->first();
    }

    public function hasSubmissionFromStudent(int $studentId): bool
    {
        return $this->submissions()->where('student_id', $studentId)->exists();
    }

    public function isGradedForStudent(int $studentId): bool
    {
        return $this->grades()->where('student_id', $studentId)->exists();
    }

    public function getAverageGrade(): float
    {
        return $this->grades()->avg('percentage') ?? 0;
    }

    public function getSubmissionCount(): int
    {
        return $this->submissions()->whereNotNull('submitted_at')->count();
    }

    public function getGradedCount(): int
    {
        return $this->grades()->count();
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', today());
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('due_date', '>', now());
    }
}
