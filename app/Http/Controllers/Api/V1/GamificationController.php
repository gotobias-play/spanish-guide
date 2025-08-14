<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class GamificationController extends Controller
{
    public function __construct(
        private GamificationService $gamificationService
    ) {}

    public function awardQuizPoints(Request $request): JsonResponse
    {
        $request->validate([
            'quiz_id' => 'required|integer|exists:quizzes,id',
            'score' => 'required|integer|min:0|max:100',
            'is_timed' => 'boolean',
            'total_time_used' => 'integer|min:0',
            'speed_bonus' => 'integer|min:0',
        ]);

        $user = $request->user();
        
        // Collect timed quiz data if provided
        $timedData = [];
        if ($request->has('is_timed')) {
            $timedData = [
                'isTimed' => $request->boolean('is_timed'),
                'totalTimeUsed' => $request->input('total_time_used'),
                'speedBonus' => $request->input('speed_bonus', 0),
            ];
        }
        
        $result = $this->gamificationService->awardPointsForQuiz(
            $user,
            $request->quiz_id,
            $request->score,
            $timedData
        );

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    public function getUserStats(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->gamificationService->getUserGamificationStats($user);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function getLeaderboard(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $leaderboard = $this->gamificationService->getLeaderboard($limit);

        return response()->json([
            'success' => true,
            'data' => $leaderboard,
        ]);
    }

    public function getUserAchievements(Request $request): JsonResponse
    {
        $user = $request->user();
        $achievements = $user->achievements()
            ->with('achievement')
            ->orderByDesc('earned_at')
            ->get()
            ->map(function ($userAchievement) {
                return [
                    'id' => $userAchievement->achievement->id,
                    'name' => $userAchievement->achievement->name,
                    'description' => $userAchievement->achievement->description,
                    'badge_icon' => $userAchievement->achievement->badge_icon,
                    'badge_color' => $userAchievement->achievement->badge_color,
                    'earned_at' => $userAchievement->earned_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $achievements,
        ]);
    }

    public function getAllAchievements(): JsonResponse
    {
        $achievements = Cache::remember('all_active_achievements', 1800, function () {
            return \App\Models\Achievement::where('is_active', true)
                ->select('id', 'name', 'description', 'badge_icon', 'badge_color', 'condition_type', 'condition_value')
                ->get();
        });

        return response()->json([
            'success' => true,
            'data' => $achievements,
        ]);
    }

    public function getUserPointsHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        $pointsHistory = $user->userPoints()
            ->orderByDesc('earned_at')
            ->limit(50)
            ->get()
            ->map(function ($points) {
                return [
                    'points' => $points->points,
                    'reason' => $points->reason,
                    'quiz_id' => $points->quiz_id,
                    'earned_at' => $points->earned_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $pointsHistory,
        ]);
    }
}