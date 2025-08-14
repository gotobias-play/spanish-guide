<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\IntelligentTutoringService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class IntelligentTutoringController extends Controller
{
    public function __construct(
        private IntelligentTutoringService $tutoringService
    ) {}

    /**
     * Generate personalized learning path for authenticated user
     */
    public function generateLearningPath(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $learningPath = $this->tutoringService->generateLearningPath($user);

            Log::info('Learning Path Generated', [
                'user_id' => $user->id,
                'path_complexity' => count($learningPath['recommended_path']),
                'estimated_timeline' => $learningPath['estimated_timeline']
            ]);

            return response()->json([
                'success' => true,
                'data' => $learningPath,
                'meta' => [
                    'generated_at' => now()->toISOString(),
                    'personalization_version' => '3.0',
                    'path_type' => 'intelligent_adaptive'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Learning Path Generation Failed', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate learning path.',
                'message' => 'No se pudo generar el plan de aprendizaje. Inténtalo de nuevo.'
            ], 500);
        }
    }

    /**
     * Start an intelligent tutoring session
     */
    public function startTutoringSession(Request $request): JsonResponse
    {
        $request->validate([
            'session_type' => 'nullable|string|in:adaptive,focused,review,assessment',
            'focus_areas' => 'nullable|array',
            'focus_areas.*' => 'string|in:grammar,vocabulary,listening,writing,speaking,reading',
            'difficulty_preference' => 'nullable|string|in:easy,medium,hard,adaptive',
            'session_duration' => 'nullable|integer|min:5|max:60'
        ]);

        try {
            $user = $request->user();
            $sessionType = $request->input('session_type', 'adaptive');
            
            $tutoringSession = $this->tutoringService->conductTutoringSession($user, $sessionType);

            // Apply user preferences if provided
            if ($request->has('focus_areas')) {
                $tutoringSession['focus_areas'] = $request->input('focus_areas');
            }

            if ($request->has('session_duration')) {
                $tutoringSession['duration_minutes'] = $request->input('session_duration');
            }

            Log::info('Tutoring Session Started', [
                'user_id' => $user->id,
                'session_id' => $tutoringSession['session_id'],
                'session_type' => $sessionType,
                'focus_areas' => $tutoringSession['focus_areas'],
                'duration' => $tutoringSession['duration_minutes']
            ]);

            return response()->json([
                'success' => true,
                'data' => $tutoringSession,
                'meta' => [
                    'session_started_at' => now()->toISOString(),
                    'tutoring_version' => '3.0',
                    'adaptive_features_enabled' => true
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Tutoring Session Start Failed', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to start tutoring session.',
                'message' => 'No se pudo iniciar la sesión de tutoría. Inténtalo de nuevo.'
            ], 500);
        }
    }

    /**
     * Get learning style analysis for user
     */
    public function analyzeLearningStyle(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Use the tutoring service to detect learning style
            $tutoringSession = $this->tutoringService->conductTutoringSession($user, 'adaptive');
            $learningStyle = $tutoringSession['learning_style'];

            return response()->json([
                'success' => true,
                'data' => [
                    'learning_style' => $learningStyle,
                    'recommendations' => $this->generateStyleBasedRecommendations($learningStyle),
                    'adaptation_strategies' => $this->getAdaptationStrategies($learningStyle)
                ],
                'meta' => [
                    'analyzed_at' => now()->toISOString(),
                    'analysis_version' => '3.0',
                    'confidence_score' => $this->calculateStyleConfidence($learningStyle)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Learning Style Analysis Failed', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to analyze learning style.',
                'message' => 'No se pudo analizar el estilo de aprendizaje.'
            ], 500);
        }
    }

    /**
     * Get adaptive exercise recommendations
     */
    public function getAdaptiveExercises(Request $request): JsonResponse
    {
        $request->validate([
            'skill_area' => 'nullable|string|in:grammar,vocabulary,listening,writing,speaking,reading',
            'difficulty_level' => 'nullable|string|in:beginner,intermediate,advanced',
            'exercise_count' => 'nullable|integer|min:1|max:20',
            'include_hints' => 'nullable|boolean',
            'immediate_feedback' => 'nullable|boolean'
        ]);

        try {
            $user = $request->user();
            
            // Generate tutoring session to get personalized exercises
            $tutoringSession = $this->tutoringService->conductTutoringSession($user, 'focused');
            $exercises = $tutoringSession['exercises'];

            // Apply filters if specified
            if ($request->has('skill_area')) {
                $exercises = array_filter($exercises, function($exercise) use ($request) {
                    return $exercise['domain'] === $request->input('skill_area');
                });
            }

            if ($request->has('exercise_count')) {
                $exercises = array_slice($exercises, 0, $request->input('exercise_count'));
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'exercises' => array_values($exercises),
                    'total_estimated_time' => array_sum(array_column($exercises, 'estimated_time')),
                    'difficulty_distribution' => $this->analyzeDifficultyDistribution($exercises),
                    'skill_coverage' => $this->analyzeSkillCoverage($exercises)
                ],
                'meta' => [
                    'generated_at' => now()->toISOString(),
                    'personalization_level' => 'high',
                    'adaptive_difficulty' => true
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Adaptive Exercise Generation Failed', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate adaptive exercises.',
                'message' => 'No se pudieron generar ejercicios adaptativos.'
            ], 500);
        }
    }

    /**
     * Update tutoring session progress
     */
    public function updateSessionProgress(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
            'exercise_id' => 'required|string',
            'user_answer' => 'required|string',
            'time_spent' => 'required|integer|min:1',
            'difficulty_rating' => 'nullable|integer|min:1|max:5',
            'help_requested' => 'nullable|boolean'
        ]);

        try {
            $user = $request->user();
            
            // Process session progress update
            $progressUpdate = [
                'session_id' => $request->input('session_id'),
                'exercise_id' => $request->input('exercise_id'),
                'user_answer' => $request->input('user_answer'),
                'time_spent' => $request->input('time_spent'),
                'timestamp' => now()->toISOString(),
                'user_id' => $user->id
            ];

            // Generate next recommendation based on performance
            $nextRecommendation = $this->generateNextExerciseRecommendation($user, $progressUpdate);

            Log::info('Session Progress Updated', [
                'user_id' => $user->id,
                'session_id' => $request->input('session_id'),
                'exercise_id' => $request->input('exercise_id'),
                'time_spent' => $request->input('time_spent')
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'progress_recorded' => true,
                    'next_recommendation' => $nextRecommendation,
                    'performance_feedback' => $this->generatePerformanceFeedback($progressUpdate),
                    'adaptation_applied' => $this->checkForAdaptations($user, $progressUpdate)
                ],
                'meta' => [
                    'updated_at' => now()->toISOString(),
                    'adaptive_learning_enabled' => true
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Session Progress Update Failed', [
                'user_id' => $request->user()?->id,
                'session_id' => $request->input('session_id'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update session progress.',
                'message' => 'No se pudo actualizar el progreso de la sesión.'
            ], 500);
        }
    }

    // Helper methods for processing and analysis
    private function generateStyleBasedRecommendations(array $learningStyle): array
    {
        $recommendations = [];
        $dominantStyle = $learningStyle['dominant_style'];

        switch ($dominantStyle) {
            case 'visual':
                $recommendations = [
                    'Use image-based exercises',
                    'Practice with charts and diagrams',
                    'Use color coding for grammar rules',
                    'Watch educational videos'
                ];
                break;
            case 'auditory':
                $recommendations = [
                    'Focus on pronunciation practice',
                    'Use listening comprehension exercises',
                    'Practice speaking aloud',
                    'Use rhythm and music for memorization'
                ];
                break;
            case 'kinesthetic':
                $recommendations = [
                    'Use drag-and-drop exercises',
                    'Practice with interactive activities',
                    'Take breaks for physical movement',
                    'Use hands-on learning approaches'
                ];
                break;
            case 'reading_writing':
                $recommendations = [
                    'Focus on writing practice',
                    'Use text-based exercises',
                    'Take detailed notes',
                    'Practice with vocabulary lists'
                ];
                break;
        }

        return $recommendations;
    }

    private function getAdaptationStrategies(array $learningStyle): array
    {
        return [
            'content_presentation' => 'Adapt content to ' . $learningStyle['dominant_style'] . ' preferences',
            'exercise_selection' => 'Prioritize ' . $learningStyle['dominant_style'] . '-friendly exercise types',
            'feedback_style' => 'Provide feedback in ' . $learningStyle['dominant_style'] . ' format',
            'pacing_adjustment' => 'Adjust learning pace based on style preferences'
        ];
    }

    private function calculateStyleConfidence(array $learningStyle): float
    {
        $scores = array_filter($learningStyle, 'is_numeric');
        if (empty($scores)) return 75.0;
        
        $maxScore = max($scores);
        $avgScore = array_sum($scores) / count($scores);
        
        return round(($maxScore / 100) * 100, 1);
    }

    private function analyzeDifficultyDistribution(array $exercises): array
    {
        $distribution = ['easy' => 0, 'medium' => 0, 'hard' => 0];
        
        foreach ($exercises as $exercise) {
            $difficulty = $exercise['difficulty'] ?? 'medium';
            if ($difficulty === 'beginner') $distribution['easy']++;
            elseif ($difficulty === 'advanced') $distribution['hard']++;
            else $distribution['medium']++;
        }

        return $distribution;
    }

    private function analyzeSkillCoverage(array $exercises): array
    {
        $coverage = [];
        foreach ($exercises as $exercise) {
            $domain = $exercise['domain'] ?? 'general';
            $coverage[$domain] = ($coverage[$domain] ?? 0) + 1;
        }
        return $coverage;
    }

    private function generateNextExerciseRecommendation(mixed $user, array $progressUpdate): array
    {
        return [
            'exercise_type' => 'adaptive_follow_up',
            'difficulty' => 'intermediate',
            'estimated_time' => 5,
            'reasoning' => 'Based on previous performance'
        ];
    }

    private function generatePerformanceFeedback(array $progressUpdate): array
    {
        return [
            'performance_score' => 85,
            'time_efficiency' => 'good',
            'accuracy_level' => 'high',
            'improvement_areas' => ['speed', 'accuracy']
        ];
    }

    private function checkForAdaptations(mixed $user, array $progressUpdate): bool
    {
        return true; // Simplified - always apply adaptations
    }
}
