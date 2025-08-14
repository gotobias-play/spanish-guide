<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subdomain',
        'domain',
        'logo_url',
        'primary_color',
        'secondary_color',
        'settings',
        'features',
        'plan',
        'status',
        'trial_ends_at',
        'max_users',
        'max_courses',
        'contact_email',
        'contact_phone',
        'description',
        'timezone',
        'locale',
        'allowed_locales',
    ];

    protected $casts = [
        'settings' => 'array',
        'features' => 'array',
        'allowed_locales' => 'array',
        'trial_ends_at' => 'datetime',
    ];

    // Relationships
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function quizRooms(): HasMany
    {
        return $this->hasMany(QuizRoom::class);
    }

    // Utility methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at > now();
    }

    public function isExpired(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at <= now();
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    public function canAddUser(): bool
    {
        return $this->users()->count() < $this->max_users;
    }

    public function canAddCourse(): bool
    {
        return $this->courses()->count() < $this->max_courses;
    }

    public function getRemainingTrialDays(): int
    {
        if (!$this->isTrial()) {
            return 0;
        }

        return max(0, now()->diffInDays($this->trial_ends_at, false));
    }

    public function getUsagePercentage(): array
    {
        return [
            'users' => [
                'current' => $this->users()->count(),
                'max' => $this->max_users,
                'percentage' => ($this->users()->count() / $this->max_users) * 100
            ],
            'courses' => [
                'current' => $this->courses()->count(),
                'max' => $this->max_courses,
                'percentage' => ($this->courses()->count() / $this->max_courses) * 100
            ]
        ];
    }

    // Scope methods
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByDomain($query, $domain)
    {
        return $query->where('domain', $domain)->orWhere('subdomain', $domain);
    }

    public function scopeBySubdomain($query, $subdomain)
    {
        return $query->where('subdomain', $subdomain);
    }

    // Static methods
    public static function findByDomain(string $domain): ?self
    {
        return self::where('domain', $domain)
            ->orWhere('subdomain', $domain)
            ->first();
    }

    public static function findBySubdomain(string $subdomain): ?self
    {
        return self::where('subdomain', $subdomain)->first();
    }
}
