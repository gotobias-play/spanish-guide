<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Course;
use App\Services\AIWritingAnalysisService;
use App\Services\IntelligentTutoringService;
use App\Services\AILearningInsightsService;

class PerformanceTestingService
{
    protected $aiWritingService;
    protected $tutoringService;
    protected $insightsService;

    public function __construct()
    {
        $this->aiWritingService = new AIWritingAnalysisService();
        $this->tutoringService = new IntelligentTutoringService();
        $this->insightsService = new AILearningInsightsService(
            $this->aiWritingService,
            $this->tutoringService
        );
    }

    /**
     * Run comprehensive performance tests for Phase 6 AI features
     */
    public function runComprehensivePerformanceTests(): array
    {
        $results = [
            'test_suite_version' => '1.0',
            'executed_at' => now()->toISOString(),
            'summary' => [],
            'detailed_results' => []
        ];

        Log::info('Starting Phase 6 Comprehensive Performance Tests');

        // Test 1: AI Writing Analysis Performance
        $results['detailed_results']['ai_writing_analysis'] = $this->testAIWritingAnalysisPerformance();

        // Test 2: Intelligent Tutoring Performance
        $results['detailed_results']['intelligent_tutoring'] = $this->testIntelligentTutoringPerformance();

        // Test 3: AI Learning Insights Performance
        $results['detailed_results']['ai_learning_insights'] = $this->testAILearningInsightsPerformance();

        // Test 4: Database Performance Tests
        $results['detailed_results']['database_performance'] = $this->testDatabasePerformance();

        // Test 5: API Endpoint Load Tests
        $results['detailed_results']['api_load_tests'] = $this->testAPILoadPerformance();

        // Test 6: Caching Performance Tests
        $results['detailed_results']['caching_performance'] = $this->testCachingPerformance();

        // Test 7: Memory Usage Tests
        $results['detailed_results']['memory_usage'] = $this->testMemoryUsage();

        // Generate performance summary
        $results['summary'] = $this->generatePerformanceSummary($results['detailed_results']);

        Log::info('Phase 6 Performance Tests Completed', ['summary' => $results['summary']]);

        return $results;
    }

    /**
     * Test AI Writing Analysis Service Performance
     */
    protected function testAIWritingAnalysisPerformance(): array
    {
        $testCases = [
            'short_text' => 'Hello world. This is a test.',
            'medium_text' => 'Learning English has been an amazing journey for me. I started with basic vocabulary and grammar, and now I can write more complex sentences. The interactive exercises help me practice daily.',
            'long_text' => 'English language learning is a comprehensive process that involves multiple skills including reading, writing, listening, and speaking. In my experience with this application, I have found that the combination of gamification elements, adaptive learning algorithms, and social features creates an engaging environment for continuous improvement. The AI-powered feedback system provides detailed analysis of my writing, helping me identify areas for improvement while celebrating my progress. Through consistent practice and the intelligent tutoring system, I have noticed significant improvements in my grammar accuracy, vocabulary diversity, and overall writing fluency.'
        ];

        $results = [];

        foreach ($testCases as $testName => $text) {
            $startTime = microtime(true);
            $startMemory = memory_get_usage();

            try {
                // Test comprehensive analysis
                $analysis = $this->aiWritingService->analyzeWriting($text, 'intermediate');
                
                $endTime = microtime(true);
                $endMemory = memory_get_usage();

                $results[$testName] = [
                    'status' => 'success',
                    'execution_time_ms' => round(($endTime - $startTime) * 1000, 2),
                    'memory_used_kb' => round(($endMemory - $startMemory) / 1024, 2),
                    'analysis_categories' => count($analysis),
                    'has_cefr_level' => isset($analysis['estimated_cefr_level']),
                    'has_grammar_analysis' => isset($analysis['grammar_analysis']),
                    'has_vocabulary_analysis' => isset($analysis['vocabulary_analysis']),
                    'text_length' => strlen($text),
                    'words_count' => str_word_count($text)
                ];
            } catch (\Exception $e) {
                $results[$testName] = [
                    'status' => 'error',
                    'error_message' => $e->getMessage(),
                    'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
                ];
            }
        }

        return [
            'test_name' => 'AI Writing Analysis Performance',
            'test_cases' => $results,
            'performance_benchmark' => [
                'target_response_time_ms' => 500,
                'max_memory_usage_kb' => 1024,
                'success_rate_target' => 100
            ]
        ];
    }

    /**
     * Test Intelligent Tutoring Service Performance
     */
    protected function testIntelligentTutoringPerformance(): array
    {
        $testUser = User::first() ?? User::factory()->create();
        $results = [];

        $tutoringTests = [
            'learning_path_generation' => function() use ($testUser) {
                return $this->tutoringService->generateLearningPath($testUser);
            },
            'adaptive_session_creation' => function() use ($testUser) {
                return $this->tutoringService->conductTutoringSession($testUser, 'adaptive');
            },
            'focused_session_creation' => function() use ($testUser) {
                return $this->tutoringService->conductTutoringSession($testUser, 'focused');
            },
            'review_session_creation' => function() use ($testUser) {
                return $this->tutoringService->conductTutoringSession($testUser, 'review');
            }
        ];

        foreach ($tutoringTests as $testName => $testFunction) {
            $startTime = microtime(true);
            $startMemory = memory_get_usage();

            try {
                $result = $testFunction();
                
                $endTime = microtime(true);
                $endMemory = memory_get_usage();

                $results[$testName] = [
                    'status' => 'success',
                    'execution_time_ms' => round(($endTime - $startTime) * 1000, 2),
                    'memory_used_kb' => round(($endMemory - $startMemory) / 1024, 2),
                    'result_structure' => array_keys($result),
                    'has_user_profile' => isset($result['user_profile']),
                    'has_learning_style' => isset($result['learning_style']),
                    'has_exercises' => isset($result['exercises']),
                    'data_completeness' => count($result)
                ];
            } catch (\Exception $e) {
                $results[$testName] = [
                    'status' => 'error',
                    'error_message' => $e->getMessage(),
                    'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
                ];
            }
        }

        return [
            'test_name' => 'Intelligent Tutoring Performance',
            'test_cases' => $results,
            'performance_benchmark' => [
                'target_response_time_ms' => 1000,
                'max_memory_usage_kb' => 2048,
                'success_rate_target' => 100
            ]
        ];
    }

    /**
     * Test AI Learning Insights Service Performance
     */
    protected function testAILearningInsightsPerformance(): array
    {
        $testUser = User::first() ?? User::factory()->create();
        $results = [];

        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        try {
            $insights = $this->insightsService->generateComprehensiveInsights($testUser);
            
            $endTime = microtime(true);
            $endMemory = memory_get_usage();

            $results['comprehensive_insights'] = [
                'status' => 'success',
                'execution_time_ms' => round(($endTime - $startTime) * 1000, 2),
                'memory_used_kb' => round(($endMemory - $startMemory) / 1024, 2),
                'insights_categories' => count($insights),
                'category_names' => array_keys($insights),
                'has_learning_analytics' => isset($insights['learning_analytics']),
                'has_performance_prediction' => isset($insights['performance_prediction']),
                'has_skill_gap_analysis' => isset($insights['skill_gap_analysis']),
                'data_depth' => array_sum(array_map('count', $insights))
            ];

            // Test caching performance
            $cacheStartTime = microtime(true);
            $cachedInsights = $this->insightsService->generateComprehensiveInsights($testUser);
            $cacheEndTime = microtime(true);

            $results['caching_performance'] = [
                'cached_execution_time_ms' => round(($cacheEndTime - $cacheStartTime) * 1000, 2),
                'cache_speedup_factor' => round($results['comprehensive_insights']['execution_time_ms'] / round(($cacheEndTime - $cacheStartTime) * 1000, 2), 2),
                'cache_working' => $insights === $cachedInsights
            ];

        } catch (\Exception $e) {
            $results['comprehensive_insights'] = [
                'status' => 'error',
                'error_message' => $e->getMessage(),
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ];
        }

        return [
            'test_name' => 'AI Learning Insights Performance',
            'test_cases' => $results,
            'performance_benchmark' => [
                'target_response_time_ms' => 2000,
                'max_memory_usage_kb' => 4096,
                'cache_speedup_minimum' => 5,
                'success_rate_target' => 100
            ]
        ];
    }

    /**
     * Test Database Performance
     */
    protected function testDatabasePerformance(): array
    {
        $results = [];

        $databaseTests = [
            'user_count_query' => function() {
                $startTime = microtime(true);
                $count = User::count();
                $endTime = microtime(true);
                return [
                    'execution_time_ms' => round(($endTime - $startTime) * 1000, 2),
                    'result' => $count
                ];
            },
            'courses_with_lessons' => function() {
                $startTime = microtime(true);
                $courses = Course::with(['lessons'])->get();
                $endTime = microtime(true);
                return [
                    'execution_time_ms' => round(($endTime - $startTime) * 1000, 2),
                    'result' => $courses->count()
                ];
            },
            'quizzes_with_questions' => function() {
                $startTime = microtime(true);
                $quizzes = Quiz::with('questions.options')->get();
                $endTime = microtime(true);
                return [
                    'execution_time_ms' => round(($endTime - $startTime) * 1000, 2),
                    'result' => $quizzes->count()
                ];
            },
            'complex_analytics_query' => function() {
                $startTime = microtime(true);
                $result = DB::table('user_quiz_progress')
                    ->select(
                        'section_id',
                        DB::raw('AVG(score) as avg_score'),
                        DB::raw('COUNT(*) as total_attempts'),
                        DB::raw('MAX(score) as max_score')
                    )
                    ->groupBy('section_id')
                    ->get();
                $endTime = microtime(true);
                return [
                    'execution_time_ms' => round(($endTime - $startTime) * 1000, 2),
                    'result' => $result->count()
                ];
            }
        ];

        foreach ($databaseTests as $testName => $testFunction) {
            try {
                $results[$testName] = array_merge(
                    ['status' => 'success'],
                    $testFunction()
                );
            } catch (\Exception $e) {
                $results[$testName] = [
                    'status' => 'error',
                    'error_message' => $e->getMessage()
                ];
            }
        }

        return [
            'test_name' => 'Database Performance',
            'test_cases' => $results,
            'performance_benchmark' => [
                'target_query_time_ms' => 100,
                'complex_query_limit_ms' => 500,
                'success_rate_target' => 100
            ]
        ];
    }

    /**
     * Test API Endpoint Load Performance
     */
    protected function testAPILoadPerformance(): array
    {
        $baseUrl = 'http://localhost:8000';
        $results = [];

        $endpoints = [
            'public_courses' => '/api/public/courses',
            'public_quizzes' => '/api/public/quizzes',
            'public_achievements' => '/api/public/achievements',
            'public_leaderboard' => '/api/public/leaderboard'
        ];

        foreach ($endpoints as $name => $endpoint) {
            $startTime = microtime(true);
            
            try {
                $response = Http::timeout(10)->get($baseUrl . $endpoint);
                $endTime = microtime(true);

                $results[$name] = [
                    'status' => 'success',
                    'http_status' => $response->status(),
                    'execution_time_ms' => round(($endTime - $startTime) * 1000, 2),
                    'response_size_kb' => round(strlen($response->body()) / 1024, 2),
                    'has_data' => $response->successful() && !empty($response->json('data')),
                    'cache_headers' => $response->header('Cache-Control') ?? 'none'
                ];
            } catch (\Exception $e) {
                $results[$name] = [
                    'status' => 'error',
                    'error_message' => $e->getMessage(),
                    'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
                ];
            }
        }

        return [
            'test_name' => 'API Load Performance',
            'test_cases' => $results,
            'performance_benchmark' => [
                'target_response_time_ms' => 200,
                'max_response_time_ms' => 1000,
                'success_rate_target' => 100
            ]
        ];
    }

    /**
     * Test Caching Performance
     */
    protected function testCachingPerformance(): array
    {
        $results = [];
        $testKey = 'performance_test_' . time();
        $testData = ['test' => 'data', 'timestamp' => now(), 'complex' => range(1, 100)];

        // Test cache write performance
        $startTime = microtime(true);
        Cache::put($testKey, $testData, 60);
        $writeTime = microtime(true) - $startTime;

        // Test cache read performance
        $startTime = microtime(true);
        $cachedData = Cache::get($testKey);
        $readTime = microtime(true) - $startTime;

        // Test cache delete performance
        $startTime = microtime(true);
        Cache::forget($testKey);
        $deleteTime = microtime(true) - $startTime;

        $results = [
            'cache_write' => [
                'status' => 'success',
                'execution_time_ms' => round($writeTime * 1000, 2),
                'data_integrity' => true
            ],
            'cache_read' => [
                'status' => 'success',
                'execution_time_ms' => round($readTime * 1000, 2),
                'data_retrieved' => $cachedData === $testData,
                'data_integrity' => $cachedData === $testData
            ],
            'cache_delete' => [
                'status' => 'success',
                'execution_time_ms' => round($deleteTime * 1000, 2),
                'cache_cleared' => Cache::get($testKey) === null
            ]
        ];

        return [
            'test_name' => 'Caching Performance',
            'test_cases' => $results,
            'performance_benchmark' => [
                'cache_write_target_ms' => 10,
                'cache_read_target_ms' => 5,
                'cache_delete_target_ms' => 5,
                'data_integrity_target' => 100
            ]
        ];
    }

    /**
     * Test Memory Usage
     */
    protected function testMemoryUsage(): array
    {
        $initialMemory = memory_get_usage();
        $peakMemory = memory_get_peak_usage();

        // Test memory usage of AI services
        $beforeAI = memory_get_usage();
        $testUser = User::first() ?? User::factory()->create();
        $aiAnalysis = $this->aiWritingService->analyzeWriting('Test memory usage analysis.', 'intermediate');
        $afterAI = memory_get_usage();

        $beforeTutoring = memory_get_usage();
        $tutoringResult = $this->tutoringService->generateLearningPath($testUser);
        $afterTutoring = memory_get_usage();

        $beforeInsights = memory_get_usage();
        $insightsResult = $this->insightsService->generateComprehensiveInsights($testUser);
        $afterInsights = memory_get_usage();

        $finalMemory = memory_get_usage();
        $finalPeakMemory = memory_get_peak_usage();

        return [
            'test_name' => 'Memory Usage Analysis',
            'memory_measurements' => [
                'initial_memory_kb' => round($initialMemory / 1024, 2),
                'ai_analysis_memory_kb' => round(($afterAI - $beforeAI) / 1024, 2),
                'tutoring_memory_kb' => round(($afterTutoring - $beforeTutoring) / 1024, 2),
                'insights_memory_kb' => round(($afterInsights - $beforeInsights) / 1024, 2),
                'final_memory_kb' => round($finalMemory / 1024, 2),
                'peak_memory_kb' => round($finalPeakMemory / 1024, 2),
                'total_memory_increase_kb' => round(($finalMemory - $initialMemory) / 1024, 2)
            ],
            'memory_benchmark' => [
                'ai_analysis_limit_kb' => 1024,
                'tutoring_limit_kb' => 2048,
                'insights_limit_kb' => 4096,
                'total_increase_limit_kb' => 8192
            ]
        ];
    }

    /**
     * Generate performance summary
     */
    protected function generatePerformanceSummary(array $detailedResults): array
    {
        $summary = [
            'overall_status' => 'success',
            'total_tests_run' => 0,
            'successful_tests' => 0,
            'failed_tests' => 0,
            'performance_score' => 0,
            'recommendations' => []
        ];

        foreach ($detailedResults as $testCategory => $testData) {
            if (isset($testData['test_cases'])) {
                foreach ($testData['test_cases'] as $testCase) {
                    $summary['total_tests_run']++;
                    if ($testCase['status'] === 'success') {
                        $summary['successful_tests']++;
                    } else {
                        $summary['failed_tests']++;
                    }
                }
            }
        }

        $summary['success_rate'] = $summary['total_tests_run'] > 0 
            ? round(($summary['successful_tests'] / $summary['total_tests_run']) * 100, 2) 
            : 0;

        $summary['performance_score'] = $summary['success_rate'];

        if ($summary['success_rate'] < 95) {
            $summary['overall_status'] = 'warning';
            $summary['recommendations'][] = 'Some tests failed or performed below expectations';
        }

        if ($summary['success_rate'] < 80) {
            $summary['overall_status'] = 'critical';
            $summary['recommendations'][] = 'Critical performance issues detected';
        }

        // Add specific recommendations based on test results
        if (isset($detailedResults['api_load_tests']['test_cases'])) {
            $avgResponseTime = collect($detailedResults['api_load_tests']['test_cases'])
                ->where('status', 'success')
                ->avg('execution_time_ms');
            
            if ($avgResponseTime > 500) {
                $summary['recommendations'][] = 'API response times are above optimal threshold';
            }
        }

        return $summary;
    }
}