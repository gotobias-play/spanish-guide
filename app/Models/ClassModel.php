<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Traits\BelongsToTenant;

class ClassModel extends Model
{
    use BelongsToTenant;

    protected $table = 'classes';

    protected $fillable = [
        'tenant_id',
        'instructor_id',
        'name',
        'description',
        'class_code',
        'max_students',
        'is_active',
        'settings',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'settings' => 'json',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($class) {
            if (empty($class->class_code)) {
                $class->class_code = static::generateUniqueClassCode();
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(ClassEnrollment::class, 'class_id');
    }

    public function activeEnrollments(): HasMany
    {
        return $this->enrollments()->where('status', 'active');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'class_enrollments', 'class_id', 'student_id')
                    ->wherePivot('status', 'active')
                    ->withPivot(['status', 'enrolled_at', 'enrollment_data'])
                    ->withTimestamps();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'class_id');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(ClassAnnouncement::class, 'class_id');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(ClassAttendance::class, 'class_id');
    }

    public function getEnrollmentCount(): int
    {
        return $this->activeEnrollments()->count();
    }

    public function getAvailableSpots(): int
    {
        return max(0, $this->max_students - $this->getEnrollmentCount());
    }

    public function isFull(): bool
    {
        return $this->getEnrollmentCount() >= $this->max_students;
    }

    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now()->toDateString();
        
        if ($this->start_date && $this->start_date > $now) {
            return false;
        }

        if ($this->end_date && $this->end_date < $now) {
            return false;
        }

        return true;
    }

    public function canEnroll(): bool
    {
        return $this->isActive() && !$this->isFull();
    }

    public static function generateUniqueClassCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (static::where('class_code', $code)->exists());

        return $code;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('start_date')
                          ->orWhere('start_date', '<=', now()->toDateString());
                    })
                    ->where(function ($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now()->toDateString());
                    });
    }

    public function scopeByInstructor($query, $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }

    public function scopeWithAvailableSpots($query)
    {
        return $query->whereRaw('(SELECT COUNT(*) FROM class_enrollments WHERE class_id = classes.id AND status = "active") < max_students');
    }
}
