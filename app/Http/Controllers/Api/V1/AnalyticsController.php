<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {}

    public function getUserAnalytics(Request $request): JsonResponse
    {
        $user = $request->user();
        $analytics = $this->analyticsService->getUserAnalytics($user);

        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }

    public function getPerformanceTrends(Request $request): JsonResponse
    {
        $user = $request->user();
        $analytics = $this->analyticsService->getUserAnalytics($user);

        return response()->json([
            'success' => true,
            'data' => [
                'performance_trends' => $analytics['performance_trends'],
                'basic_stats' => $analytics['basic_stats']
            ]
        ]);
    }

    public function getSubjectAnalysis(Request $request): JsonResponse
    {
        $user = $request->user();
        $analytics = $this->analyticsService->getUserAnalytics($user);

        return response()->json([
            'success' => true,
            'data' => [
                'subject_analysis' => $analytics['subject_analysis']
            ]
        ]);
    }

    public function getLearningInsights(Request $request): JsonResponse
    {
        $user = $request->user();
        $analytics = $this->analyticsService->getUserAnalytics($user);

        return response()->json([
            'success' => true,
            'data' => [
                'learning_insights' => $analytics['learning_insights'],
                'goals' => $analytics['goals']
            ]
        ]);
    }
}