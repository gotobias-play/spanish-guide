<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SpeechAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SpeechRecognitionController extends Controller
{
    private $speechAnalysisService;

    public function __construct(SpeechAnalysisService $speechAnalysisService)
    {
        $this->speechAnalysisService = $speechAnalysisService;
    }

    /**
     * Analyze pronunciation from audio recording
     */
    public function analyzePronunciation(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'audio_data' => 'required|string',
                'target_text' => 'required|string|max:500',
                'language' => 'string|in:en,es',
                'exercise_type' => 'string|in:word,sentence,paragraph,dialogue',
                'difficulty_level' => 'string|in:beginner,intermediate,advanced',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid input data',
                    'errors' => $validator->errors(),
                ], 400);
            }

            $audioData = $request->input('audio_data');
            $targetText = $request->input('target_text');
            $language = $request->input('language', 'en');
            $exerciseType = $request->input('exercise_type', 'sentence');
            $difficultyLevel = $request->input('difficulty_level', 'intermediate');

            // Analyze pronunciation
            $analysis = $this->speechAnalysisService->analyzePronunciation(
                $audioData,
                $targetText,
                $language
            );

            // Add exercise context
            $analysis['exercise_context'] = [
                'type' => $exerciseType,
                'difficulty' => $difficultyLevel,
                'target_language' => $language,
            ];

            // Save analysis result if user is authenticated
            if (Auth::check()) {
                $this->savePronunciationResult(Auth::id(), $analysis, $request->all());
            }

            return response()->json([
                'success' => true,
                'analysis' => $analysis,
                'message' => 'Pronunciation analysis completed successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Pronunciation analysis error', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request_data' => $request->except(['audio_data']),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Analysis failed. Please try again.',
                'error' => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get pronunciation exercises for practice
     */
    public function getPronunciationExercises(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category' => 'string|in:vowels,consonants,words,sentences,phonetic_drills',
                'difficulty' => 'string|in:beginner,intermediate,advanced',
                'focus_area' => 'string|in:th_sounds,r_sounds,v_sounds,general',
                'limit' => 'integer|min:1|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 400);
            }

            $category = $request->input('category', 'words');
            $difficulty = $request->input('difficulty', 'intermediate');
            $focusArea = $request->input('focus_area', 'general');
            $limit = $request->input('limit', 20);

            $exercises = $this->generatePronunciationExercises($category, $difficulty, $focusArea, $limit);

            return response()->json([
                'success' => true,
                'exercises' => $exercises,
                'metadata' => [
                    'category' => $category,
                    'difficulty' => $difficulty,
                    'focus_area' => $focusArea,
                    'total_exercises' => count($exercises),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Get pronunciation exercises error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load pronunciation exercises',
            ], 500);
        }
    }

    /**
     * Get user's pronunciation progress and history
     */
    public function getPronunciationProgress(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required',
                ], 401);
            }

            $userId = Auth::id();
            $days = $request->input('days', 30);

            $progress = $this->calculatePronunciationProgress($userId, $days);

            return response()->json([
                'success' => true,
                'progress' => $progress,
            ]);

        } catch (\Exception $e) {
            Log::error('Get pronunciation progress error', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load pronunciation progress',
            ], 500);
        }
    }

    /**
     * Start a guided pronunciation session
     */
    public function startPronunciationSession(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'session_type' => 'required|string|in:quick_practice,focused_drill,assessment,conversation',
                'duration' => 'integer|min:5|max:60',
                'focus_phonemes' => 'array',
                'difficulty_level' => 'string|in:beginner,intermediate,advanced',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 400);
            }

            $sessionType = $request->input('session_type');
            $duration = $request->input('duration', 15);
            $focusPhonemes = $request->input('focus_phonemes', []);
            $difficultyLevel = $request->input('difficulty_level', 'intermediate');

            $session = $this->createPronunciationSession($sessionType, $duration, $focusPhonemes, $difficultyLevel);

            // Save session for authenticated users
            if (Auth::check()) {
                $this->savePronunciationSession(Auth::id(), $session);
            }

            return response()->json([
                'success' => true,
                'session' => $session,
                'message' => 'Pronunciation session started successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Start pronunciation session error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to start pronunciation session',
            ], 500);
        }
    }

    /**
     * Get real-time pronunciation feedback
     */
    public function getRealtimeFeedback(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'audio_chunk' => 'required|string',
                'session_id' => 'string',
                'timestamp' => 'integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 400);
            }

            $audioChunk = $request->input('audio_chunk');
            $sessionId = $request->input('session_id');
            $timestamp = $request->input('timestamp', time());

            $feedback = $this->generateRealtimeFeedback($audioChunk, $sessionId);

            return response()->json([
                'success' => true,
                'feedback' => $feedback,
                'timestamp' => $timestamp,
            ]);

        } catch (\Exception $e) {
            Log::error('Realtime feedback error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate realtime feedback',
            ], 500);
        }
    }

    /**
     * Compare user pronunciation with native speaker
     */
    public function compareWithNative(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_audio' => 'required|string',
                'native_audio_id' => 'required|string',
                'text' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 400);
            }

            $userAudio = $request->input('user_audio');
            $nativeAudioId = $request->input('native_audio_id');
            $text = $request->input('text');

            $comparison = $this->performNativeComparison($userAudio, $nativeAudioId, $text);

            return response()->json([
                'success' => true,
                'comparison' => $comparison,
            ]);

        } catch (\Exception $e) {
            Log::error('Native comparison error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to compare with native pronunciation',
            ], 500);
        }
    }

    // Private helper methods

    private function savePronunciationResult($userId, $analysis, $requestData)
    {
        try {
            // In a real implementation, save to pronunciation_results table
            Log::info('Pronunciation result saved', [
                'user_id' => $userId,
                'overall_score' => $analysis['overall_score'],
                'target_text' => $analysis['target_text'],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save pronunciation result', ['error' => $e->getMessage()]);
        }
    }

    private function generatePronunciationExercises($category, $difficulty, $focusArea, $limit)
    {
        $exercises = [];

        $exerciseBank = [
            'vowels' => [
                'beginner' => [
                    ['text' => 'cat', 'phonetic' => '/kæt/', 'audio_id' => 'vowel_cat'],
                    ['text' => 'bat', 'phonetic' => '/bæt/', 'audio_id' => 'vowel_bat'],
                    ['text' => 'hat', 'phonetic' => '/hæt/', 'audio_id' => 'vowel_hat'],
                ],
                'intermediate' => [
                    ['text' => 'about', 'phonetic' => '/əˈbaʊt/', 'audio_id' => 'vowel_about'],
                    ['text' => 'receive', 'phonetic' => '/rɪˈsiːv/', 'audio_id' => 'vowel_receive'],
                ],
                'advanced' => [
                    ['text' => 'beautiful', 'phonetic' => '/ˈbjuːtɪfəl/', 'audio_id' => 'vowel_beautiful'],
                ],
            ],
            'consonants' => [
                'beginner' => [
                    ['text' => 'think', 'phonetic' => '/θɪŋk/', 'audio_id' => 'cons_think'],
                    ['text' => 'very', 'phonetic' => '/ˈveri/', 'audio_id' => 'cons_very'],
                ],
                'intermediate' => [
                    ['text' => 'measure', 'phonetic' => '/ˈmeʒər/', 'audio_id' => 'cons_measure'],
                    ['text' => 'challenge', 'phonetic' => '/ˈtʃælɪndʒ/', 'audio_id' => 'cons_challenge'],
                ],
                'advanced' => [
                    ['text' => 'thoroughly', 'phonetic' => '/ˈθʌroli/', 'audio_id' => 'cons_thoroughly'],
                ],
            ],
            'words' => [
                'beginner' => [
                    ['text' => 'Hello, how are you?', 'type' => 'greeting', 'audio_id' => 'word_hello'],
                    ['text' => 'Thank you very much', 'type' => 'politeness', 'audio_id' => 'word_thanks'],
                ],
                'intermediate' => [
                    ['text' => 'I would like to make a reservation', 'type' => 'formal', 'audio_id' => 'word_reservation'],
                    ['text' => 'Could you please repeat that?', 'type' => 'clarification', 'audio_id' => 'word_repeat'],
                ],
                'advanced' => [
                    ['text' => 'The implementation of this methodology requires careful consideration', 'type' => 'academic', 'audio_id' => 'word_implementation'],
                ],
            ],
            'sentences' => [
                'beginner' => [
                    ['text' => 'The weather is nice today', 'focus' => 'basic_conversation', 'audio_id' => 'sent_weather'],
                    ['text' => 'I like to read books', 'focus' => 'hobbies', 'audio_id' => 'sent_books'],
                ],
                'intermediate' => [
                    ['text' => 'She sells seashells by the seashore', 'focus' => 'tongue_twister', 'audio_id' => 'sent_seashells'],
                    ['text' => 'The quick brown fox jumps over the lazy dog', 'focus' => 'pangram', 'audio_id' => 'sent_fox'],
                ],
                'advanced' => [
                    ['text' => 'Red leather, yellow leather, red leather, yellow leather', 'focus' => 'articulation', 'audio_id' => 'sent_leather'],
                ],
            ],
        ];

        $categoryExercises = $exerciseBank[$category][$difficulty] ?? [];
        
        // Filter by focus area if specified
        if ($focusArea !== 'general') {
            $categoryExercises = array_filter($categoryExercises, function($exercise) use ($focusArea) {
                return isset($exercise['focus']) && strpos($exercise['focus'], $focusArea) !== false;
            });
        }

        // Add exercise metadata
        foreach (array_slice($categoryExercises, 0, $limit) as $index => $exercise) {
            $exercises[] = array_merge($exercise, [
                'id' => $category . '_' . $difficulty . '_' . ($index + 1),
                'category' => $category,
                'difficulty' => $difficulty,
                'estimated_duration' => rand(30, 120), // seconds
                'instructions' => $this->getExerciseInstructions($category, $exercise),
            ]);
        }

        return $exercises;
    }

    private function calculatePronunciationProgress($userId, $days)
    {
        // Simulate pronunciation progress calculation
        return [
            'overall_improvement' => rand(10, 30),
            'sessions_completed' => rand(5, 20),
            'average_score' => rand(70, 90),
            'strongest_areas' => ['vowel_sounds', 'basic_words'],
            'improvement_areas' => ['th_sounds', 'r_sounds'],
            'recent_scores' => array_map(function() {
                return rand(65, 95);
            }, range(1, min($days, 14))),
            'practice_streak' => rand(1, 7),
            'total_practice_time' => rand(120, 600), // minutes
        ];
    }

    private function createPronunciationSession($sessionType, $duration, $focusPhonemes, $difficultyLevel)
    {
        $sessionId = 'session_' . uniqid();
        
        $exercises = $this->generateSessionExercises($sessionType, $duration, $focusPhonemes, $difficultyLevel);

        return [
            'session_id' => $sessionId,
            'type' => $sessionType,
            'duration' => $duration,
            'difficulty_level' => $difficultyLevel,
            'focus_phonemes' => $focusPhonemes,
            'exercises' => $exercises,
            'estimated_completion_time' => $duration * 60, // seconds
            'instructions' => $this->getSessionInstructions($sessionType),
            'created_at' => now()->toISOString(),
        ];
    }

    private function generateSessionExercises($sessionType, $duration, $focusPhonemes, $difficultyLevel)
    {
        $exerciseCount = intval($duration / 3); // Roughly 3 minutes per exercise
        $exercises = [];

        for ($i = 0; $i < $exerciseCount; $i++) {
            $exercises[] = [
                'exercise_id' => 'ex_' . ($i + 1),
                'type' => $sessionType,
                'text' => $this->getExerciseText($sessionType, $difficultyLevel, $focusPhonemes),
                'phonetic' => $this->getPhoneticTranscription($sessionType),
                'target_duration' => 180, // 3 minutes
                'focus_areas' => $focusPhonemes,
            ];
        }

        return $exercises;
    }

    private function savePronunciationSession($userId, $session)
    {
        try {
            // In a real implementation, save to pronunciation_sessions table
            Log::info('Pronunciation session saved', [
                'user_id' => $userId,
                'session_id' => $session['session_id'],
                'type' => $session['type'],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save pronunciation session', ['error' => $e->getMessage()]);
        }
    }

    private function generateRealtimeFeedback($audioChunk, $sessionId)
    {
        // Simulate real-time pronunciation feedback
        return [
            'session_id' => $sessionId,
            'confidence' => rand(70, 95),
            'clarity' => rand(75, 90),
            'pace' => rand(80, 95),
            'suggestions' => [
                'Slow down slightly for clearer pronunciation',
                'Focus on the "th" sound',
            ],
            'instant_score' => rand(70, 90),
            'next_action' => 'continue',
        ];
    }

    private function performNativeComparison($userAudio, $nativeAudioId, $text)
    {
        // Simulate comparison with native speaker pronunciation
        return [
            'similarity_score' => rand(65, 85),
            'pronunciation_match' => rand(70, 90),
            'rhythm_match' => rand(60, 85),
            'intonation_match' => rand(70, 80),
            'areas_of_difference' => [
                'word_stress' => 'Focus on stressing the first syllable',
                'vowel_sounds' => 'Practice the /ɑ/ sound in "father"',
            ],
            'recommendations' => [
                'Listen to the native recording again',
                'Practice word stress patterns',
                'Record yourself multiple times',
            ],
            'native_audio_features' => [
                'duration' => rand(2, 5),
                'pitch_range' => 'moderate',
                'speech_rate' => 'normal',
            ],
        ];
    }

    private function getExerciseInstructions($category, $exercise)
    {
        $instructions = [
            'vowels' => 'Focus on the vowel sound. Listen to the example and repeat clearly.',
            'consonants' => 'Pay attention to tongue and lip position for this consonant sound.',
            'words' => 'Pronounce each word clearly, focusing on stress patterns.',
            'sentences' => 'Practice natural rhythm and intonation in this sentence.',
        ];

        return $instructions[$category] ?? 'Practice pronunciation carefully and clearly.';
    }

    private function getSessionInstructions($sessionType)
    {
        $instructions = [
            'quick_practice' => 'Short pronunciation drills to warm up your speaking muscles.',
            'focused_drill' => 'Intensive practice on specific pronunciation challenges.',
            'assessment' => 'Comprehensive evaluation of your pronunciation skills.',
            'conversation' => 'Natural conversation practice with pronunciation feedback.',
        ];

        return $instructions[$sessionType] ?? 'Practice pronunciation exercises at your own pace.';
    }

    private function getExerciseText($sessionType, $difficultyLevel, $focusPhonemes)
    {
        $texts = [
            'quick_practice' => [
                'beginner' => 'The cat sat on the mat',
                'intermediate' => 'She sells seashells by the seashore',
                'advanced' => 'The sixth sick sheik\'s sixth sheep\'s sick',
            ],
            'focused_drill' => [
                'beginner' => 'Think about this thing',
                'intermediate' => 'The weather in the Netherlands',
                'advanced' => 'Thoroughly thoughtful thinking',
            ],
        ];

        return $texts[$sessionType][$difficultyLevel] ?? 'Practice pronunciation';
    }

    private function getPhoneticTranscription($sessionType)
    {
        // Simulate phonetic transcription based on session type
        $transcriptions = [
            'quick_practice' => '/ðə kæt sæt ɒn ðə mæt/',
            'focused_drill' => '/θɪŋk əˈbaʊt ðɪs θɪŋ/',
            'assessment' => '/kəmˌpriːˈhensɪv əˌsesmənt/',
            'conversation' => '/ˈnætʃərəl ˌkɒnvəˈseɪʃən/',
        ];

        return $transcriptions[$sessionType] ?? '/prækˈtɪs prəˌnʌnsiˈeɪʃən/';
    }
}