<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AssignmentSubmission extends Model
{
    protected $fillable = [
        'assignment_id',
        'student_id',
        'content',
        'notes',
        'attachments',
        'status',
        'submitted_at',
        'is_late',
    ];

    protected $casts = [
        'content' => 'json',
        'attachments' => 'json',
        'submitted_at' => 'datetime',
        'is_late' => 'boolean',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function grade(): HasOne
    {
        return $this->hasOne(Grade::class, 'assignment_id', 'assignment_id')
                    ->where('student_id', $this->student_id ?? 0);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return in_array($this->status, ['submitted', 'graded', 'returned']);
    }

    public function isGraded(): bool
    {
        return in_array($this->status, ['graded', 'returned']);
    }

    public function submit(): void
    {
        $dueDate = $this->assignment->due_date;
        $isLate = $dueDate && now()->isAfter($dueDate);

        $this->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'is_late' => $isLate,
        ]);
    }

    public function markAsGraded(): void
    {
        $this->update([
            'status' => 'graded',
        ]);
    }

    public function return(): void
    {
        $this->update([
            'status' => 'returned',
        ]);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }

    public function scopeLate($query)
    {
        return $query->where('is_late', true);
    }

    public function scopeOnTime($query)
    {
        return $query->where('is_late', false);
    }
}
