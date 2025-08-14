<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LearningRecommendation;
use App\Models\UserSkillLevel;
use App\Services\AdaptiveLearningService;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LearningController extends Controller
{
    protected $adaptiveLearningService;
    protected $gamificationService;

    public function __construct(AdaptiveLearningService $adaptiveLearningService, GamificationService $gamificationService)
    {
        $this->adaptiveLearningService = $adaptiveLearningService;
        $this->gamificationService = $gamificationService;
    }

    /**
     * Get user's personalized learning recommendations
     */
    public function getRecommendations(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        try {
            $recommendations = $this->adaptiveLearningService->generatePersonalizedRecommendations($user);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'recommendations' => $recommendations->toArray(),
                    'count' => $recommendations->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating recommendations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's skill levels and progress
     */
    public function getSkillLevels(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        try {
            $skillLevels = UserSkillLevel::where('user_id', $user->id)
                ->orderBy('mastery_score', 'desc')
                ->get();

            $skillStats = [
                'total_skills' => $skillLevels->count(),
                'mastered_skills' => $skillLevels->where('mastery_score', '>=', 80)->count(),
                'developing_skills' => $skillLevels->where('mastery_score', '>=', 60)
                    ->where('mastery_score', '<', 80)->count(),
                'struggling_skills' => $skillLevels->where('mastery_score', '<', 60)->count(),
                'average_mastery' => $skillLevels->avg('mastery_score') ?? 0,
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'skill_levels' => $skillLevels,
                    'statistics' => $skillStats,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching skill levels',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user skill after quiz completion
     */
    public function updateSkillLevel(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $validatedData = $request->validate([
            'quiz_id' => 'required|integer',
            'correct_answers' => 'required|integer|min:0',
            'total_questions' => 'required|integer|min:1',
            'completion_time' => 'nullable|integer|min:1', // in seconds
            'accuracy' => 'required|numeric|between:0,100',
        ]);

        try {
            // Update skill level using the adaptive learning service
            $skillLevel = $this->adaptiveLearningService->updateSkillLevel($user, $validatedData);

            // Award points through gamification service
            $pointsData = [
                'score' => $validatedData['accuracy'],
                'correct_answers' => $validatedData['correct_answers'],
                'total_questions' => $validatedData['total_questions'],
                'quiz_id' => $validatedData['quiz_id'],
                'is_timed' => $request->boolean('is_timed', false),
                'speed_bonus' => $request->input('speed_bonus', 0),
                'total_time_used' => $validatedData['completion_time'] ?? 0,
            ];

            $this->gamificationService->awardQuizPoints($user, $pointsData);

            return response()->json([
                'success' => true,
                'data' => [
                    'skill_level' => $skillLevel,
                    'recommendations_generated' => true,
                ],
                'message' => 'Skill level updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating skill level',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark recommendation as viewed
     */
    public function markRecommendationAsViewed(Request $request, int $recommendationId): JsonResponse
    {
        $user = Auth::user();
        
        try {
            $recommendation = LearningRecommendation::where('id', $recommendationId)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $recommendation->markAsViewed();

            return response()->json([
                'success' => true,
                'message' => 'Recommendation marked as viewed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking recommendation as viewed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark recommendation as completed
     */
    public function markRecommendationAsCompleted(Request $request, int $recommendationId): JsonResponse
    {
        $user = Auth::user();
        
        try {
            $recommendation = LearningRecommendation::where('id', $recommendationId)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $recommendation->markAsCompleted();

            return response()->json([
                'success' => true,
                'message' => 'Recommendation marked as completed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking recommendation as completed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Dismiss a recommendation
     */
    public function dismissRecommendation(Request $request, int $recommendationId): JsonResponse
    {
        $user = Auth::user();
        
        try {
            $recommendation = LearningRecommendation::where('id', $recommendationId)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $recommendation->dismiss();

            return response()->json([
                'success' => true,
                'message' => 'Recommendation dismissed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error dismissing recommendation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get spaced repetition schedule
     */
    public function getSpacedRepetitionSchedule(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        try {
            $schedule = $this->adaptiveLearningService->getSpacedRepetitionSchedule($user);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'schedule' => $schedule,
                    'due_count' => count($schedule),
                    'urgent_count' => count(array_filter($schedule, fn($item) => $item['urgency'] >= 80)),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching spaced repetition schedule',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get learning insights and analytics
     */
    public function getLearningInsights(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        try {
            $skillLevels = UserSkillLevel::where('user_id', $user->id)->get();
            
            // Performance trends over time
            $recentPerformance = $skillLevels->map(function ($skill) {
                return [
                    'skill_topic' => $skill->skill_topic,
                    'mastery_score' => $skill->mastery_score,
                    'difficulty_level' => $skill->difficulty_level,
                    'last_practiced' => $skill->last_practiced_at,
                    'performance_trend' => $this->calculatePerformanceTrend($skill),
                ];
            });

            // Learning velocity (improvement rate)
            $learningVelocity = $this->calculateLearningVelocity($skillLevels);

            // Predicted mastery timeline
            $masteryPredictions = $this->predictMasteryTimeline($skillLevels);

            // Learning preferences analysis
            $preferences = $this->analyzeLearningPreferences($user, $skillLevels);

            return response()->json([
                'success' => true,
                'data' => [
                    'performance_trends' => $recentPerformance,
                    'learning_velocity' => $learningVelocity,
                    'mastery_predictions' => $masteryPredictions,
                    'learning_preferences' => $preferences,
                    'generated_at' => now()->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating learning insights',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate performance trend for a skill
     */
    protected function calculatePerformanceTrend(UserSkillLevel $skill): string
    {
        $history = $skill->performance_history ?? [];
        
        if (count($history) < 5) {
            return 'insufficient_data';
        }

        $recent = array_slice($history, -5);
        $older = array_slice($history, -10, 5);

        $recentAccuracy = count(array_filter($recent, fn($item) => $item['is_correct'])) / count($recent);
        $olderAccuracy = count($older) > 0 
            ? count(array_filter($older, fn($item) => $item['is_correct'])) / count($older) 
            : $recentAccuracy;

        if ($recentAccuracy > $olderAccuracy + 0.1) {
            return 'improving';
        } elseif ($recentAccuracy < $olderAccuracy - 0.1) {
            return 'declining';
        } else {
            return 'stable';
        }
    }

    /**
     * Calculate overall learning velocity
     */
    protected function calculateLearningVelocity(Collection $skillLevels): array
    {
        $totalProgress = $skillLevels->sum('mastery_score');
        $skillCount = $skillLevels->count();
        
        if ($skillCount === 0) {
            return ['velocity' => 0, 'description' => 'No data available'];
        }

        $averageMastery = $totalProgress / $skillCount;
        
        // Calculate velocity based on recent activity
        $recentlyActive = $skillLevels->filter(function ($skill) {
            return $skill->last_practiced_at && $skill->last_practiced_at >= now()->subWeek();
        });

        $velocity = $recentlyActive->count() * ($averageMastery / 100) * 10;

        return [
            'velocity' => round($velocity, 2),
            'description' => $this->getVelocityDescription($velocity),
            'active_skills' => $recentlyActive->count(),
            'average_mastery' => round($averageMastery, 2),
        ];
    }

    /**
     * Get velocity description
     */
    protected function getVelocityDescription(float $velocity): string
    {
        if ($velocity >= 8) return 'Excellent progress';
        if ($velocity >= 6) return 'Good progress';
        if ($velocity >= 4) return 'Steady progress';
        if ($velocity >= 2) return 'Slow progress';
        return 'Minimal progress';
    }

    /**
     * Predict mastery timeline for skills
     */
    protected function predictMasteryTimeline(Collection $skillLevels): array
    {
        $predictions = [];

        foreach ($skillLevels as $skill) {
            if ($skill->mastery_score >= 80) {
                continue; // Already mastered
            }

            $currentProgress = $skill->mastery_score;
            $recentSessions = count($skill->performance_history ?? []);
            
            if ($recentSessions < 3) {
                continue; // Insufficient data for prediction
            }

            // Simple linear regression based on recent progress
            $progressRate = $this->estimateProgressRate($skill);
            $remainingProgress = 80 - $currentProgress;
            
            if ($progressRate > 0) {
                $estimatedDays = ceil($remainingProgress / $progressRate);
                $predictions[] = [
                    'skill_topic' => $skill->skill_topic,
                    'current_mastery' => $currentProgress,
                    'estimated_days_to_mastery' => min($estimatedDays, 365), // Cap at 1 year
                    'confidence' => $this->calculatePredictionConfidence($skill),
                ];
            }
        }

        return $predictions;
    }

    /**
     * Estimate progress rate for a skill
     */
    protected function estimateProgressRate(UserSkillLevel $skill): float
    {
        $history = $skill->performance_history ?? [];
        
        if (count($history) < 5) {
            return 0;
        }

        // Simple rate calculation: recent performance vs older performance
        $recent = array_slice($history, -5);
        $recentAccuracy = count(array_filter($recent, fn($item) => $item['is_correct'])) / count($recent);
        
        return $recentAccuracy * 2; // Approximate points per session
    }

    /**
     * Calculate prediction confidence
     */
    protected function calculatePredictionConfidence(UserSkillLevel $skill): string
    {
        $historyCount = count($skill->performance_history ?? []);
        $consistency = $skill->consecutive_correct / max($skill->total_attempts, 1);
        
        if ($historyCount >= 10 && $consistency >= 0.7) {
            return 'high';
        } elseif ($historyCount >= 5 && $consistency >= 0.5) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Analyze user's learning preferences
     */
    protected function analyzeLearningPreferences(User $user, Collection $skillLevels): array
    {
        $preferences = [
            'preferred_difficulty' => 'intermediate',
            'preferred_session_length' => 'medium',
            'peak_performance_time' => 'unknown',
            'learning_style' => 'balanced',
        ];

        // Analyze difficulty preferences based on performance
        $bestPerformingLevels = $skillLevels->groupBy('difficulty_level')
            ->map(fn($skills) => $skills->avg('mastery_score'))
            ->sortDesc();

        if ($bestPerformingLevels->isNotEmpty()) {
            $preferences['preferred_difficulty'] = $bestPerformingLevels->keys()->first();
        }

        // Analyze session length preferences (placeholder for future implementation)
        $preferences['preferred_session_length'] = 'medium'; // 5-7 questions

        return $preferences;
    }
}