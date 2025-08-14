<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialActivity extends Model
{
    protected $fillable = [
        'user_id',
        'activity_type',
        'title',
        'description',
        'activity_data',
        'is_public',
        'occurred_at',
    ];

    protected $casts = [
        'activity_data' => 'array',
        'is_public' => 'boolean',
        'occurred_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a new social activity
     */
    public static function create_activity(
        User $user,
        string $activityType,
        string $title,
        ?string $description = null,
        ?array $activityData = null,
        bool $isPublic = true
    ): self {
        return self::create([
            'user_id' => $user->id,
            'activity_type' => $activityType,
            'title' => $title,
            'description' => $description,
            'activity_data' => $activityData,
            'is_public' => $isPublic,
            'occurred_at' => now(),
        ]);
    }

    /**
     * Get activity icon based on type
     */
    public function getIconAttribute(): string
    {
        $icons = [
            'quiz_completed' => '📝',
            'achievement_earned' => '🏆',
            'certificate_earned' => '🎓',
            'streak_milestone' => '🔥',
            'challenge_sent' => '⚔️',
            'challenge_won' => '👑',
            'friend_added' => '👥',
        ];

        return $icons[$this->activity_type] ?? '📍';
    }

    /**
     * Get activity color for UI
     */
    public function getColorAttribute(): string
    {
        $colors = [
            'quiz_completed' => 'blue',
            'achievement_earned' => 'yellow',
            'certificate_earned' => 'purple',
            'streak_milestone' => 'red',
            'challenge_sent' => 'orange',
            'challenge_won' => 'green',
            'friend_added' => 'indigo',
        ];

        return $colors[$this->activity_type] ?? 'gray';
    }

    /**
     * Get formatted time ago
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->occurred_at->diffForHumans();
    }

    /**
     * Get activities for user's social feed (friends' activities)
     */
    public static function getFeedForUser(User $user, int $limit = 20)
    {
        // Get user's friends
        $friendIds = $user->getFriendIds();
        
        if (empty($friendIds)) {
            return collect();
        }

        return self::with('user')
            ->whereIn('user_id', $friendIds)
            ->where('is_public', true)
            ->orderBy('occurred_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get user's own activities
     */
    public static function getUserActivities(User $user, int $limit = 10)
    {
        return self::where('user_id', $user->id)
            ->orderBy('occurred_at', 'desc')
            ->limit($limit)
            ->get();
    }
}