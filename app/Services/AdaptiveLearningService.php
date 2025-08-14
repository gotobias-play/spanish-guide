<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSkillLevel;
use App\Models\LearningRecommendation;
use App\Models\Quiz;
use App\Models\UserQuizProgress;
use App\Models\UserStreak;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdaptiveLearningService
{
    /**
     * Update user skill level after quiz completion
     */
    public function updateSkillLevel(User $user, array $quizData): UserSkillLevel
    {
        $skillLevel = UserSkillLevel::firstOrCreate([
            'user_id' => $user->id,
            'skill_category' => $this->mapQuizToSkillCategory($quizData['quiz_id']),
            'skill_topic' => $this->mapQuizToSkillTopic($quizData['quiz_id']),
        ]);

        $accuracy = ($quizData['correct_answers'] / $quizData['total_questions']) * 100;
        $isCorrect = $accuracy >= 60; // Consider 60%+ as "correct" session
        $responseTime = $quizData['completion_time'] ?? null;

        $skillLevel->updatePerformance($isCorrect, $responseTime);

        // Generate new recommendations based on updated skill level
        $this->generateRecommendationsForSkill($user, $skillLevel);

        return $skillLevel;
    }

    /**
     * Generate personalized learning recommendations for a user
     */
    public function generatePersonalizedRecommendations(User $user): Collection
    {
        // Clear expired recommendations
        $this->clearExpiredRecommendations($user);

        $recommendations = collect();

        // 1. Skill improvement recommendations
        $strugglingSkills = $this->getStrugglingSkills($user);
        foreach ($strugglingSkills as $skill) {
            $recommendations->push(
                LearningRecommendation::createSkillImprovement($user, $skill)
            );
        }

        // 2. Review reminders for skills that need maintenance
        $skillsNeedingReview = $this->getSkillsNeedingReview($user);
        foreach ($skillsNeedingReview as $skill) {
            $recommendations->push(
                LearningRecommendation::createReviewReminder($user, $skill)
            );
        }

        // 3. Streak motivation
        $currentStreak = $this->getUserCurrentStreak($user);
        if ($currentStreak <= 2 || $this->shouldMotivateStreak($user)) {
            $recommendations->push(
                LearningRecommendation::createStreakMotivation($user, $currentStreak)
            );
        }

        // 4. New content recommendations
        $this->generateNewContentRecommendations($user, $recommendations);

        // 5. Difficulty adjustment recommendations
        $this->generateDifficultyAdjustmentRecommendations($user, $recommendations);

        // Limit to top 5 recommendations by priority
        return $recommendations->sortBy('priority')->take(5);
    }

    /**
     * Get user's struggling skills (mastery < 60%)
     */
    protected function getStrugglingSkills(User $user): Collection
    {
        return UserSkillLevel::where('user_id', $user->id)
            ->where('mastery_score', '<', 60)
            ->where('total_attempts', '>=', 3) // Only recommend after some attempts
            ->orderBy('mastery_score')
            ->limit(3)
            ->get();
    }

    /**
     * Get skills that need review based on time since last practice
     */
    protected function getSkillsNeedingReview(User $user): Collection
    {
        return UserSkillLevel::where('user_id', $user->id)
            ->where('mastery_score', '>=', 60) // Only review skills that are somewhat mastered
            ->get()
            ->filter(function ($skill) {
                return $skill->needsReview();
            })
            ->sortByDesc('last_practiced_at')
            ->take(2);
    }

    /**
     * Generate recommendations for a specific skill
     */
    protected function generateRecommendationsForSkill(User $user, UserSkillLevel $skill): void
    {
        // Check if we already have recent recommendations for this skill
        $hasRecentRecommendation = LearningRecommendation::where('user_id', $user->id)
            ->where('action_data->skill_topic', $skill->skill_topic)
            ->where('created_at', '>', now()->subDays(2))
            ->where('is_dismissed', false)
            ->where('is_completed', false)
            ->exists();

        if ($hasRecentRecommendation) {
            return;
        }

        // Generate appropriate recommendation based on skill level
        if ($skill->mastery_score < 40) {
            LearningRecommendation::createSkillImprovement($user, $skill);
        } elseif ($skill->needsReview()) {
            LearningRecommendation::createReviewReminder($user, $skill);
        }
    }

    /**
     * Generate new content recommendations based on user progress
     */
    protected function generateNewContentRecommendations(User $user, Collection $recommendations): void
    {
        // Get user's strongest skills to recommend advancing to new content
        $strongestSkills = UserSkillLevel::where('user_id', $user->id)
            ->where('mastery_score', '>=', 80)
            ->orderByDesc('mastery_score')
            ->limit(2)
            ->get();

        foreach ($strongestSkills as $skill) {
            $nextLevelContent = $this->getNextLevelContent($skill);
            
            if ($nextLevelContent) {
                $recommendation = LearningRecommendation::create([
                    'user_id' => $user->id,
                    'recommendation_type' => 'new_content',
                    'title' => "Nuevo contenido: {$nextLevelContent['title']}",
                    'description' => "Dominas {$skill->skill_topic} muy bien. ¡Es hora de avanzar a contenido más desafiante!",
                    'action_type' => 'quiz',
                    'action_data' => $nextLevelContent,
                    'priority' => 3,
                    'confidence_score' => 75,
                    'expires_at' => now()->addWeek(),
                ]);
                
                $recommendations->push($recommendation);
            }
        }
    }

    /**
     * Generate difficulty adjustment recommendations
     */
    protected function generateDifficultyAdjustmentRecommendations(User $user, Collection $recommendations): void
    {
        // Find skills where user is consistently performing too well or too poorly
        $skillsNeedingAdjustment = UserSkillLevel::where('user_id', $user->id)
            ->where('total_attempts', '>=', 5)
            ->get()
            ->filter(function ($skill) {
                return ($skill->mastery_score >= 90 && $skill->consecutive_correct >= 10) ||
                       ($skill->mastery_score < 30 && $skill->accuracy_rate < 40);
            });

        foreach ($skillsNeedingAdjustment as $skill) {
            $adjustmentType = $skill->mastery_score >= 90 ? 'increase' : 'decrease';
            
            $recommendation = LearningRecommendation::create([
                'user_id' => $user->id,
                'recommendation_type' => 'difficulty_adjustment',
                'title' => $adjustmentType === 'increase' 
                    ? "¡Aumenta el desafío en {$skill->skill_topic}!"
                    : "Practica lo básico en {$skill->skill_topic}",
                'description' => $adjustmentType === 'increase'
                    ? "Dominas este tema. Intenta preguntas más difíciles para seguir creciendo."
                    : "Este tema es desafiante. Practica con preguntas más básicas para construir confianza.",
                'action_type' => 'quiz',
                'action_data' => [
                    'skill_category' => $skill->skill_category,
                    'skill_topic' => $skill->skill_topic,
                    'difficulty_level' => $adjustmentType === 'increase' 
                        ? $this->getNextDifficultyLevel($skill->difficulty_level)
                        : $this->getPreviousDifficultyLevel($skill->difficulty_level),
                    'adjustment_type' => $adjustmentType,
                ],
                'priority' => 2,
                'confidence_score' => 85,
                'expires_at' => now()->addDays(5),
            ]);
            
            $recommendations->push($recommendation);
        }
    }

    /**
     * Map quiz ID to skill category
     */
    protected function mapQuizToSkillCategory(int $quizId): string
    {
        // This should be based on your quiz structure
        // For now, we'll use a simple mapping
        $mappings = [
            1 => 'grammar',        // Basic Grammar
            2 => 'daily_life',     // Daily Life
            3 => 'vocabulary',     // City/Locations
            4 => 'vocabulary',     // Restaurant/Food
            5 => 'grammar',        // Questions
            6 => 'mixed',          // Advanced Interactive
            7 => 'grammar',        // Speed Grammar
            8 => 'daily_life',     // Daily Life Time Challenge
        ];

        return $mappings[$quizId] ?? 'general';
    }

    /**
     * Map quiz ID to specific skill topic
     */
    protected function mapQuizToSkillTopic(int $quizId): string
    {
        $mappings = [
            1 => 'to_be_to_have',
            2 => 'present_simple',
            3 => 'prepositions',
            4 => 'quantifiers',
            5 => 'wh_questions',
            6 => 'mixed_skills',
            7 => 'grammar_speed',
            8 => 'daily_life_timed',
        ];

        return $mappings[$quizId] ?? 'general_english';
    }

    /**
     * Get next level content for a skill
     */
    protected function getNextLevelContent(UserSkillLevel $skill): ?array
    {
        // This would be enhanced to suggest actual next-level quizzes
        $nextLevelMappings = [
            'to_be_to_have' => [
                'title' => 'Present Simple Advanced',
                'quiz_id' => 2,
                'difficulty_level' => 'intermediate',
            ],
            'present_simple' => [
                'title' => 'Past Simple Tense',
                'quiz_id' => null, // Would be created
                'difficulty_level' => 'intermediate',
            ],
            'prepositions' => [
                'title' => 'Advanced Prepositions',
                'quiz_id' => null,
                'difficulty_level' => 'advanced',
            ],
        ];

        return $nextLevelMappings[$skill->skill_topic] ?? null;
    }

    /**
     * Get user's current study streak
     */
    protected function getUserCurrentStreak(User $user): int
    {
        $userStreak = UserStreak::where('user_id', $user->id)->first();
        return $userStreak ? $userStreak->current_streak : 0;
    }

    /**
     * Check if user should be motivated for streak
     */
    protected function shouldMotivateStreak(User $user): bool
    {
        $lastActivity = UserQuizProgress::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->first();

        if (!$lastActivity) {
            return true; // No activity, definitely motivate
        }

        // Motivate if last activity was more than 18 hours ago
        return $lastActivity->created_at < now()->subHours(18);
    }

    /**
     * Clear expired or completed recommendations
     */
    protected function clearExpiredRecommendations(User $user): void
    {
        LearningRecommendation::where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('expires_at', '<', now())
                      ->orWhere('is_completed', true);
            })
            ->delete();
    }

    /**
     * Get next difficulty level
     */
    protected function getNextDifficultyLevel(string $currentLevel): string
    {
        $levels = ['beginner', 'elementary', 'intermediate', 'advanced'];
        $currentIndex = array_search($currentLevel, $levels);
        
        return $currentIndex !== false && $currentIndex < count($levels) - 1
            ? $levels[$currentIndex + 1]
            : $currentLevel;
    }

    /**
     * Get previous difficulty level
     */
    protected function getPreviousDifficultyLevel(string $currentLevel): string
    {
        $levels = ['beginner', 'elementary', 'intermediate', 'advanced'];
        $currentIndex = array_search($currentLevel, $levels);
        
        return $currentIndex !== false && $currentIndex > 0
            ? $levels[$currentIndex - 1]
            : $currentLevel;
    }

    /**
     * Get personalized spaced repetition schedule
     */
    public function getSpacedRepetitionSchedule(User $user): array
    {
        $skills = UserSkillLevel::where('user_id', $user->id)->get();
        $schedule = [];

        foreach ($skills as $skill) {
            $interval = $this->calculateSpacedRepetitionInterval($skill);
            
            if ($skill->last_practiced_at && $skill->last_practiced_at->addDays($interval) <= now()) {
                $schedule[] = [
                    'skill' => $skill,
                    'due_date' => $skill->last_practiced_at->addDays($interval),
                    'urgency' => $this->calculateUrgency($skill, $interval),
                ];
            }
        }

        // Sort by urgency (higher urgency first)
        usort($schedule, function ($a, $b) {
            return $b['urgency'] - $a['urgency'];
        });

        return $schedule;
    }

    /**
     * Calculate spaced repetition interval based on skill mastery
     */
    protected function calculateSpacedRepetitionInterval(UserSkillLevel $skill): int
    {
        $baseInterval = 1; // 1 day base

        if ($skill->mastery_score >= 90) {
            return $baseInterval * 14; // 2 weeks for mastered skills
        } elseif ($skill->mastery_score >= 75) {
            return $baseInterval * 7;  // 1 week for good skills
        } elseif ($skill->mastery_score >= 60) {
            return $baseInterval * 3;  // 3 days for okay skills
        } else {
            return $baseInterval;      // Daily for struggling skills
        }
    }

    /**
     * Calculate urgency score for spaced repetition
     */
    protected function calculateUrgency(UserSkillLevel $skill, int $interval): int
    {
        if (!$skill->last_practiced_at) {
            return 100; // Highest urgency for never practiced
        }

        $daysSinceDue = now()->diffInDays($skill->last_practiced_at->addDays($interval));
        $urgency = min(100, $daysSinceDue * 10); // 10 points per day overdue

        // Boost urgency for struggling skills
        if ($skill->mastery_score < 40) {
            $urgency += 25;
        }

        return $urgency;
    }
}