<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\BelongsToTenant;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'tenant_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function userPoints(): HasMany
    {
        return $this->hasMany(UserPoints::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function streak(): HasOne
    {
        return $this->hasOne(UserStreak::class);
    }

    public function getTotalPoints(): int
    {
        return UserPoints::getTotalPointsForUser($this->id);
    }

    public function getCurrentStreak(): int
    {
        return $this->streak?->current_streak ?? 0;
    }

    public function getLongestStreak(): int
    {
        return $this->streak?->longest_streak ?? 0;
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    // Friendship relationships
    public function sentFriendRequests(): HasMany
    {
        return $this->hasMany(\App\Models\Friendship::class, 'requester_id');
    }

    public function receivedFriendRequests(): HasMany
    {
        return $this->hasMany(\App\Models\Friendship::class, 'addressee_id');
    }

    // Challenge relationships
    public function sentChallenges(): HasMany
    {
        return $this->hasMany(\App\Models\Challenge::class, 'challenger_id');
    }

    public function receivedChallenges(): HasMany
    {
        return $this->hasMany(\App\Models\Challenge::class, 'challenged_id');
    }

    // Social activities
    public function socialActivities(): HasMany
    {
        return $this->hasMany(\App\Models\SocialActivity::class);
    }

    /**
     * Get all friends (accepted friendships)
     */
    public function getFriends()
    {
        $sentAccepted = $this->sentFriendRequests()
            ->where('status', 'accepted')
            ->with('addressee')
            ->get()
            ->pluck('addressee');

        $receivedAccepted = $this->receivedFriendRequests()
            ->where('status', 'accepted')
            ->with('requester')
            ->get()
            ->pluck('requester');

        return $sentAccepted->merge($receivedAccepted);
    }

    /**
     * Get friend IDs for database queries
     */
    public function getFriendIds(): array
    {
        return $this->getFriends()->pluck('id')->toArray();
    }

    /**
     * Get pending friend requests sent to this user
     */
    public function getPendingFriendRequests()
    {
        return $this->receivedFriendRequests()
            ->where('status', 'pending')
            ->with('requester')
            ->get();
    }

    /**
     * Check if users are friends
     */
    public function isFriendWith(User $user): bool
    {
        return \App\Models\Friendship::where(function ($query) use ($user) {
            $query->where('requester_id', $this->id)
                  ->where('addressee_id', $user->id);
        })->orWhere(function ($query) use ($user) {
            $query->where('requester_id', $user->id)
                  ->where('addressee_id', $this->id);
        })->where('status', 'accepted')->exists();
    }

    /**
     * Send friend request to another user
     */
    public function sendFriendRequest(User $user): ?Friendship
    {
        // Don't send request to self
        if ($this->id === $user->id) {
            return null;
        }

        // Check if friendship already exists
        if (\App\Models\Friendship::existsBetween($this, $user)) {
            return null;
        }

        return \App\Models\Friendship::create([
            'requester_id' => $this->id,
            'addressee_id' => $user->id,
            'requested_at' => now(),
        ]);
    }

    // Instructor relationships
    public function instructorRoles(): HasMany
    {
        return $this->hasMany(\App\Models\InstructorRole::class);
    }

    public function instructorClasses(): HasMany
    {
        return $this->hasMany(\App\Models\ClassModel::class, 'instructor_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(\App\Models\Assignment::class, 'instructor_id');
    }

    public function gradesGiven(): HasMany
    {
        return $this->hasMany(\App\Models\Grade::class, 'graded_by');
    }

    // Student relationships
    public function classEnrollments(): HasMany
    {
        return $this->hasMany(\App\Models\ClassEnrollment::class, 'student_id');
    }

    public function assignmentSubmissions(): HasMany
    {
        return $this->hasMany(\App\Models\AssignmentSubmission::class, 'student_id');
    }

    public function gradesReceived(): HasMany
    {
        return $this->hasMany(\App\Models\Grade::class, 'student_id');
    }

    /**
     * Check if user has an active instructor role
     */
    public function isInstructor(): bool
    {
        return $this->instructorRoles()
            ->active()
            ->exists();
    }

    /**
     * Get active instructor role
     */
    public function getActiveInstructorRole(): ?\App\Models\InstructorRole
    {
        return $this->instructorRoles()
            ->active()
            ->first();
    }

    /**
     * Check if user has specific instructor permission
     */
    public function hasInstructorPermission(string $permission): bool
    {
        $role = $this->getActiveInstructorRole();
        return $role && $role->hasPermission($permission);
    }
}
