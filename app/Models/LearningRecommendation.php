<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningRecommendation extends Model
{
    protected $fillable = [
        'user_id',
        'recommendation_type',
        'title',
        'description',
        'action_type',
        'action_data',
        'priority',
        'confidence_score',
        'is_dismissed',
        'is_completed',
        'expires_at',
        'viewed_at',
        'completed_at',
    ];

    protected $casts = [
        'action_data' => 'array',
        'confidence_score' => 'decimal:2',
        'is_dismissed' => 'boolean',
        'is_completed' => 'boolean',
        'expires_at' => 'datetime',
        'viewed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark recommendation as viewed
     */
    public function markAsViewed(): void
    {
        if (!$this->viewed_at) {
            $this->viewed_at = now();
            $this->save();
        }
    }

    /**
     * Mark recommendation as completed
     */
    public function markAsCompleted(): void
    {
        $this->is_completed = true;
        $this->completed_at = now();
        $this->save();
    }

    /**
     * Dismiss the recommendation
     */
    public function dismiss(): void
    {
        $this->is_dismissed = true;
        $this->save();
    }

    /**
     * Check if recommendation is still valid
     */
    public function isValid(): bool
    {
        if ($this->is_dismissed || $this->is_completed) {
            return false;
        }

        if ($this->expires_at && now()->isAfter($this->expires_at)) {
            return false;
        }

        return true;
    }

    /**
     * Get recommendation icon based on type
     */
    public function getIconAttribute(): string
    {
        $icons = [
            'skill_improvement' => '📈',
            'review_reminder' => '🔄',
            'new_content' => '✨',
            'difficulty_adjustment' => '⚖️',
            'streak_motivation' => '🔥',
        ];

        return $icons[$this->recommendation_type] ?? '💡';
    }

    /**
     * Get recommendation color based on priority
     */
    public function getColorAttribute(): string
    {
        $colors = [
            1 => 'red',    // High priority
            2 => 'orange', // Medium-high
            3 => 'yellow', // Medium
            4 => 'blue',   // Low-medium
            5 => 'gray',   // Low priority
        ];

        return $colors[$this->priority] ?? 'gray';
    }

    /**
     * Get priority label
     */
    public function getPriorityLabelAttribute(): string
    {
        $labels = [
            1 => 'Alta',
            2 => 'Media-Alta', 
            3 => 'Media',
            4 => 'Media-Baja',
            5 => 'Baja',
        ];

        return $labels[$this->priority] ?? 'Desconocida';
    }

    /**
     * Get active recommendations for a user
     */
    public static function getActiveForUser(User $user, int $limit = 5)
    {
        return self::where('user_id', $user->id)
            ->where('is_dismissed', false)
            ->where('is_completed', false)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->orderBy('priority')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Create a skill improvement recommendation
     */
    public static function createSkillImprovement(User $user, UserSkillLevel $skill): self
    {
        $title = "Practica {$skill->skill_topic}";
        $description = "Tu dominio en {$skill->skill_topic} es del {$skill->mastery_score}%. ¡Practica más para mejorar!";
        
        return self::create([
            'user_id' => $user->id,
            'recommendation_type' => 'skill_improvement',
            'title' => $title,
            'description' => $description,
            'action_type' => 'practice',
            'action_data' => [
                'skill_category' => $skill->skill_category,
                'skill_topic' => $skill->skill_topic,
                'difficulty_level' => $skill->difficulty_level,
                'question_count' => $skill->getRecommendedPracticeCount(),
            ],
            'priority' => $skill->mastery_score < 40 ? 1 : 2,
            'confidence_score' => 85,
            'expires_at' => now()->addDays(3),
        ]);
    }

    /**
     * Create a review reminder recommendation
     */
    public static function createReviewReminder(User $user, UserSkillLevel $skill): self
    {
        $daysSince = now()->diffInDays($skill->last_practiced_at);
        
        return self::create([
            'user_id' => $user->id,
            'recommendation_type' => 'review_reminder',
            'title' => "Repasa {$skill->skill_topic}",
            'description' => "No has practicado {$skill->skill_topic} en {$daysSince} días. ¡Es hora de un repaso!",
            'action_type' => 'review',
            'action_data' => [
                'skill_category' => $skill->skill_category,
                'skill_topic' => $skill->skill_topic,
                'difficulty_level' => $skill->difficulty_level,
                'question_count' => 3, // Light review
            ],
            'priority' => $skill->mastery_score >= 80 ? 3 : 2,
            'confidence_score' => 90,
            'expires_at' => now()->addDays(2),
        ]);
    }

    /**
     * Create streak motivation recommendation
     */
    public static function createStreakMotivation(User $user, int $currentStreak): self
    {
        if ($currentStreak === 0) {
            $title = "¡Comienza una nueva racha!";
            $description = "Completa un quiz hoy para comenzar tu racha de estudio.";
        } else {
            $title = "¡Mantén tu racha de {$currentStreak} días!";
            $description = "Estás haciendo un gran trabajo. ¡Completa otro quiz para continuar tu racha!";
        }

        return self::create([
            'user_id' => $user->id,
            'recommendation_type' => 'streak_motivation',
            'title' => $title,
            'description' => $description,
            'action_type' => 'quiz',
            'action_data' => [
                'current_streak' => $currentStreak,
                'suggested_quiz_count' => 1,
            ],
            'priority' => $currentStreak === 0 ? 2 : 3,
            'confidence_score' => 80,
            'expires_at' => now()->addHours(18), // Expires at end of day
        ]);
    }
}