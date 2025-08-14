<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PerformanceTestingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PerformanceTestController extends Controller
{
    protected $performanceTestingService;

    public function __construct(PerformanceTestingService $performanceTestingService)
    {
        $this->performanceTestingService = $performanceTestingService;
    }

    /**
     * Run comprehensive performance test suite
     */
    public function runComprehensiveTests(): JsonResponse
    {
        try {
            $results = $this->performanceTestingService->runComprehensivePerformanceTests();

            return response()->json([
                'success' => true,
                'message' => 'Phase 6 comprehensive performance tests completed successfully',
                'data' => $results,
                'meta' => [
                    'test_suite_version' => $results['test_suite_version'],
                    'executed_at' => $results['executed_at'],
                    'total_test_categories' => count($results['detailed_results']),
                    'overall_status' => $results['summary']['overall_status'] ?? 'unknown'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Performance test execution failed',
                'message' => $e->getMessage(),
                'meta' => [
                    'error_type' => 'performance_test_exception',
                    'timestamp' => now()->toISOString()
                ]
            ], 500);
        }
    }

    /**
     * Get performance test results summary
     */
    public function getTestSummary(): JsonResponse
    {
        try {
            $results = $this->performanceTestingService->runComprehensivePerformanceTests();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $results['summary'],
                    'test_categories' => array_keys($results['detailed_results']),
                    'execution_info' => [
                        'test_suite_version' => $results['test_suite_version'],
                        'executed_at' => $results['executed_at']
                    ]
                ],
                'meta' => [
                    'response_type' => 'performance_summary',
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Performance summary generation failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Run specific performance test category
     */
    public function runSpecificTest(Request $request, string $testCategory): JsonResponse
    {
        $validCategories = [
            'ai_writing_analysis',
            'intelligent_tutoring', 
            'ai_learning_insights',
            'database_performance',
            'api_load_tests',
            'caching_performance',
            'memory_usage'
        ];

        if (!in_array($testCategory, $validCategories)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid test category',
                'message' => "Test category must be one of: " . implode(', ', $validCategories),
                'available_categories' => $validCategories
            ], 400);
        }

        try {
            $fullResults = $this->performanceTestingService->runComprehensivePerformanceTests();
            $specificResult = $fullResults['detailed_results'][$testCategory] ?? null;

            if (!$specificResult) {
                return response()->json([
                    'success' => false,
                    'error' => 'Test category not found in results',
                    'message' => "No results found for test category: {$testCategory}"
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => "Performance test for {$testCategory} completed successfully",
                'data' => $specificResult,
                'meta' => [
                    'test_category' => $testCategory,
                    'executed_at' => $fullResults['executed_at'],
                    'test_suite_version' => $fullResults['test_suite_version']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Specific performance test failed',
                'message' => $e->getMessage(),
                'test_category' => $testCategory
            ], 500);
        }
    }

    /**
     * Get available test categories and descriptions
     */
    public function getAvailableTests(): JsonResponse
    {
        $testCategories = [
            'ai_writing_analysis' => [
                'name' => 'AI Writing Analysis Performance',
                'description' => 'Tests AI writing analysis service with different text lengths and complexity',
                'metrics' => ['execution_time_ms', 'memory_used_kb', 'analysis_completeness'],
                'target_response_time_ms' => 500
            ],
            'intelligent_tutoring' => [
                'name' => 'Intelligent Tutoring Performance', 
                'description' => 'Tests tutoring service for learning path generation and session creation',
                'metrics' => ['execution_time_ms', 'memory_used_kb', 'data_completeness'],
                'target_response_time_ms' => 1000
            ],
            'ai_learning_insights' => [
                'name' => 'AI Learning Insights Performance',
                'description' => 'Tests comprehensive learning insights generation and caching effectiveness',
                'metrics' => ['execution_time_ms', 'memory_used_kb', 'cache_speedup_factor'],
                'target_response_time_ms' => 2000
            ],
            'database_performance' => [
                'name' => 'Database Query Performance',
                'description' => 'Tests database query performance for common operations and analytics',
                'metrics' => ['query_execution_time_ms', 'result_count'],
                'target_query_time_ms' => 100
            ],
            'api_load_tests' => [
                'name' => 'API Endpoint Load Performance',
                'description' => 'Tests API endpoint response times and data integrity under load',
                'metrics' => ['execution_time_ms', 'http_status', 'response_size_kb'],
                'target_response_time_ms' => 200
            ],
            'caching_performance' => [
                'name' => 'Caching System Performance',
                'description' => 'Tests cache read/write performance and data integrity',
                'metrics' => ['cache_write_time_ms', 'cache_read_time_ms', 'data_integrity'],
                'target_cache_time_ms' => 10
            ],
            'memory_usage' => [
                'name' => 'Memory Usage Analysis',
                'description' => 'Analyzes memory consumption patterns for AI services and operations',
                'metrics' => ['initial_memory_kb', 'peak_memory_kb', 'memory_increase_kb'],
                'memory_limit_kb' => 8192
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'available_test_categories' => $testCategories,
                'total_categories' => count($testCategories),
                'usage' => [
                    'run_all_tests' => 'POST /api/performance/comprehensive',
                    'run_specific_test' => 'POST /api/performance/test/{category}',
                    'get_summary' => 'GET /api/performance/summary'
                ]
            ],
            'meta' => [
                'response_type' => 'available_tests',
                'timestamp' => now()->toISOString()
            ]
        ]);
    }
}
