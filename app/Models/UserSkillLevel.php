<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSkillLevel extends Model
{
    protected $fillable = [
        'user_id',
        'skill_category',
        'skill_topic',
        'difficulty_level',
        'mastery_score',
        'correct_answers',
        'total_attempts',
        'accuracy_rate',
        'last_practiced_at',
        'consecutive_correct',
        'performance_history',
    ];

    protected $casts = [
        'mastery_score' => 'decimal:2',
        'accuracy_rate' => 'decimal:2',
        'performance_history' => 'array',
        'last_practiced_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Update skill level based on performance
     */
    public function updatePerformance(bool $isCorrect, int $responseTime = null): void
    {
        $this->total_attempts++;
        
        if ($isCorrect) {
            $this->correct_answers++;
            $this->consecutive_correct++;
        } else {
            $this->consecutive_correct = 0;
        }

        // Calculate accuracy rate
        $this->accuracy_rate = ($this->correct_answers / $this->total_attempts) * 100;

        // Update performance history
        $history = $this->performance_history ?? [];
        $history[] = [
            'is_correct' => $isCorrect,
            'response_time' => $responseTime,
            'timestamp' => now()->toISOString(),
        ];

        // Keep only last 20 attempts
        if (count($history) > 20) {
            $history = array_slice($history, -20);
        }
        
        $this->performance_history = $history;
        $this->last_practiced_at = now();

        // Calculate mastery score using weighted average
        $this->calculateMasteryScore();
        
        // Check if difficulty level should be adjusted
        $this->adjustDifficultyLevel();

        $this->save();
    }

    /**
     * Calculate mastery score based on recent performance
     */
    protected function calculateMasteryScore(): void
    {
        $recentPerformance = array_slice($this->performance_history ?? [], -10);
        
        if (empty($recentPerformance)) {
            $this->mastery_score = $this->accuracy_rate;
            return;
        }

        $weights = [
            'accuracy' => 0.5,
            'consistency' => 0.3,
            'speed' => 0.2,
        ];

        // Accuracy component
        $accuracyScore = $this->accuracy_rate;

        // Consistency component (based on consecutive correct answers)
        $consistencyScore = min($this->consecutive_correct * 10, 100);

        // Speed component (based on average response time)
        $speedScore = $this->calculateSpeedScore($recentPerformance);

        $this->mastery_score = 
            ($accuracyScore * $weights['accuracy']) +
            ($consistencyScore * $weights['consistency']) +
            ($speedScore * $weights['speed']);

        $this->mastery_score = min($this->mastery_score, 100);
    }

    /**
     * Calculate speed score based on response times
     */
    protected function calculateSpeedScore(array $recentPerformance): float
    {
        $responseTimes = array_filter(array_column($recentPerformance, 'response_time'));
        
        if (empty($responseTimes)) {
            return 50; // Default score if no timing data
        }

        $avgResponseTime = array_sum($responseTimes) / count($responseTimes);
        
        // Good response times: 5-15 seconds, excellent: < 5 seconds
        if ($avgResponseTime <= 5) {
            return 100;
        } elseif ($avgResponseTime <= 15) {
            return 100 - (($avgResponseTime - 5) * 8); // Linear decrease
        } else {
            return max(20, 100 - $avgResponseTime * 2); // Minimum 20 points
        }
    }

    /**
     * Adjust difficulty level based on mastery score
     */
    protected function adjustDifficultyLevel(): void
    {
        $currentLevel = $this->difficulty_level;
        
        // Progression rules
        if ($this->mastery_score >= 85 && $this->consecutive_correct >= 5) {
            switch ($currentLevel) {
                case 'beginner':
                    $this->difficulty_level = 'elementary';
                    break;
                case 'elementary':
                    $this->difficulty_level = 'intermediate';
                    break;
                case 'intermediate':
                    $this->difficulty_level = 'advanced';
                    break;
            }
        }
        // Regression rules (if struggling)
        elseif ($this->mastery_score < 40 && $this->accuracy_rate < 50) {
            switch ($currentLevel) {
                case 'advanced':
                    $this->difficulty_level = 'intermediate';
                    break;
                case 'intermediate':
                    $this->difficulty_level = 'elementary';
                    break;
                case 'elementary':
                    $this->difficulty_level = 'beginner';
                    break;
            }
        }
    }

    /**
     * Get skill level color for UI
     */
    public function getLevelColorAttribute(): string
    {
        $colors = [
            'beginner' => 'green',
            'elementary' => 'blue', 
            'intermediate' => 'purple',
            'advanced' => 'red',
        ];

        return $colors[$this->difficulty_level] ?? 'gray';
    }

    /**
     * Get mastery level description
     */
    public function getMasteryLevelAttribute(): string
    {
        $score = $this->mastery_score;
        
        if ($score >= 90) return 'Experto';
        if ($score >= 75) return 'Avanzado';
        if ($score >= 60) return 'Intermedio';
        if ($score >= 40) return 'Básico';
        
        return 'Principiante';
    }

    /**
     * Check if skill needs review (hasn't been practiced recently)
     */
    public function needsReview(): bool
    {
        if (!$this->last_practiced_at) {
            return true;
        }

        $daysSinceLastPractice = now()->diffInDays($this->last_practiced_at);
        
        // Review intervals based on mastery score
        if ($this->mastery_score >= 80) {
            return $daysSinceLastPractice >= 7; // Review weekly for mastered skills
        } elseif ($this->mastery_score >= 60) {
            return $daysSinceLastPractice >= 3; // Review every 3 days for good skills
        } else {
            return $daysSinceLastPractice >= 1; // Daily review for struggling skills
        }
    }

    /**
     * Get recommended practice questions count
     */
    public function getRecommendedPracticeCount(): int
    {
        if ($this->mastery_score >= 80) {
            return 3; // Fewer questions for mastered skills
        } elseif ($this->mastery_score >= 60) {
            return 5; // Moderate practice
        } else {
            return 8; // More practice for struggling skills
        }
    }
}