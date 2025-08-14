<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPoints;
use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Models\UserStreak;
use App\Models\Certificate;
use App\Models\Course;
use Carbon\Carbon;

class GamificationService
{
    public function awardPointsForQuiz(User $user, int $quizId, int $score, array $timedData = []): array
    {
        $points = $this->calculateQuizPoints($score);
        $speedBonus = $timedData['speedBonus'] ?? 0;
        $totalPoints = $points + $speedBonus;
        
        $reason = "Quiz completed with {$score}% score";
        if ($speedBonus > 0) {
            $reason .= " (+{$speedBonus} speed bonus)";
        }
        
        UserPoints::create([
            'user_id' => $user->id,
            'points' => $totalPoints,
            'reason' => $reason,
            'quiz_id' => $quizId,
            'earned_at' => now(),
        ]);

        // Update streak
        $this->updateStreak($user);
        
        // Check achievements (include timed quiz context)
        $achievementContext = [
            'quiz_completed' => true,
            'quiz_score' => $score,
            'quiz_id' => $quizId,
            'is_timed' => isset($timedData['isTimed']) ? $timedData['isTimed'] : false,
            'time_used' => $timedData['totalTimeUsed'] ?? null,
            'speed_bonus' => $speedBonus,
        ];
        
        $newAchievements = $this->checkAchievements($user, $achievementContext);

        return [
            'points_earned' => $totalPoints,
            'base_points' => $points,
            'speed_bonus' => $speedBonus,
            'total_points' => $user->getTotalPoints(),
            'new_achievements' => $newAchievements,
        ];
    }

    protected function calculateQuizPoints(int $score): int
    {
        // Base points calculation
        if ($score >= 100) {
            return 50; // Perfect score
        } elseif ($score >= 80) {
            return 30; // Good score
        } elseif ($score >= 60) {
            return 20; // Passing score
        } else {
            return 10; // Participation points
        }
    }

    protected function updateStreak(User $user): void
    {
        $streak = UserStreak::firstOrCreate(
            ['user_id' => $user->id],
            [
                'current_streak' => 0,
                'longest_streak' => 0,
                'last_activity_date' => null,
            ]
        );

        $streak->updateStreak();
    }

    public function checkAchievements(User $user, array $context = []): array
    {
        $achievements = Achievement::where('is_active', true)->get();
        $newAchievements = [];

        foreach ($achievements as $achievement) {
            // Skip if user already has this achievement
            if ($user->achievements()->where('achievement_id', $achievement->id)->exists()) {
                continue;
            }

            if ($this->checkAchievementCondition($user, $achievement, $context)) {
                $userAchievement = UserAchievement::create([
                    'user_id' => $user->id,
                    'achievement_id' => $achievement->id,
                    'earned_at' => now(),
                ]);

                $newAchievements[] = $achievement;
            }
        }

        return $newAchievements;
    }

    protected function checkAchievementCondition(User $user, Achievement $achievement, array $context): bool
    {
        $conditionValue = $achievement->condition_value;

        switch ($achievement->condition_type) {
            case 'quizzes_completed':
                $completedCount = UserPoints::where('user_id', $user->id)
                    ->whereNotNull('quiz_id')
                    ->distinct('quiz_id')
                    ->count();
                return $completedCount >= $conditionValue['count'];

            case 'perfect_score':
                return isset($context['quiz_score']) && $context['quiz_score'] >= $conditionValue['score'];

            case 'points':
                return $user->getTotalPoints() >= $conditionValue['total'];

            case 'streak':
                $streak = UserStreak::where('user_id', $user->id)->first();
                return $streak && $streak->current_streak >= $conditionValue['days'];

            case 'course_completion':
                // Check if user has completed all quizzes for a specific course
                // This would need to be implemented based on your course structure
                return false; // Placeholder

            case 'speed_bonus_earned':
                return isset($context['speed_bonus']) && $context['speed_bonus'] >= $conditionValue['min_bonus'];

            case 'timed_quizzes_completed':
                // Count completed timed quizzes
                $timedQuizCount = UserPoints::where('user_id', $user->id)
                    ->whereNotNull('quiz_id')
                    ->where('reason', 'like', '%speed bonus%')
                    ->distinct('quiz_id')
                    ->count();
                return $timedQuizCount >= $conditionValue['count'];

            case 'timed_perfect_score':
                return isset($context['is_timed']) && 
                       $context['is_timed'] && 
                       isset($context['quiz_score']) && 
                       $context['quiz_score'] >= $conditionValue['score'];

            case 'certificates_earned':
                $certificateCount = Certificate::where('user_id', $user->id)->count();
                return $certificateCount >= $conditionValue['count'];

            case 'all_courses_certified':
                $totalCourses = Course::where('is_published', true)->count();
                $userCertificates = Certificate::where('user_id', $user->id)->count();
                return $totalCourses > 0 && $userCertificates >= $totalCourses;

            case 'excellent_certificate':
                $excellentCount = Certificate::where('user_id', $user->id)
                    ->where('average_score', '>=', $conditionValue['min_score'])
                    ->count();
                return $excellentCount > 0;

            case 'excellent_certificates':
                $excellentCount = Certificate::where('user_id', $user->id)
                    ->where('average_score', '>=', $conditionValue['min_score'])
                    ->count();
                return $excellentCount >= $conditionValue['count'];

            case 'quick_course_completion':
                $quickCertificates = Certificate::where('user_id', $user->id)
                    ->whereRaw('DATEDIFF(course_completed_at, course_started_at) <= ?', [$conditionValue['max_days']])
                    ->count();
                return $quickCertificates > 0;

            default:
                return false;
        }
    }

    public function getUserGamificationStats(User $user): array
    {
        $streak = UserStreak::where('user_id', $user->id)->first();
        $achievements = $user->achievements()->with('achievement')->get();
        $totalPoints = $user->getTotalPoints();
        $quizzesCompleted = UserPoints::where('user_id', $user->id)
            ->whereNotNull('quiz_id')
            ->distinct('quiz_id')
            ->count();

        return [
            'total_points' => $totalPoints,
            'current_streak' => $streak ? $streak->current_streak : 0,
            'longest_streak' => $streak ? $streak->longest_streak : 0,
            'quizzes_completed' => $quizzesCompleted,
            'achievements' => $achievements,
            'achievement_count' => $achievements->count(),
        ];
    }

    public function getLeaderboard(int $limit = 10): array
    {
        return User::select('users.*')
            ->selectRaw('COALESCE(SUM(user_points.points), 0) as total_points')
            ->leftJoin('user_points', 'users.id', '=', 'user_points.user_id')
            ->groupBy('users.id', 'users.name', 'users.email', 'users.email_verified_at', 'users.created_at', 'users.updated_at', 'users.is_admin')
            ->orderByDesc('total_points')
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'total_points' => (int) $user->total_points,
                    'achievements_count' => $user->achievements()->count(),
                ];
            })
            ->toArray();
    }
}