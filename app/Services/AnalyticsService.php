<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPoints;
use App\Models\UserQuizProgress;
use App\Models\Quiz;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AnalyticsService
{
    public function getUserAnalytics(User $user): array
    {
        $basicStats = $this->getBasicStats($user);
        $performanceTrends = $this->getPerformanceTrends($user);
        $subjectAnalysis = $this->getSubjectAnalysis($user);
        $timeAnalysis = $this->getTimeAnalysis($user);
        $streakAnalysis = $this->getStreakAnalysis($user);
        $achievements = $this->getAchievementProgress($user);

        return [
            'basic_stats' => $basicStats,
            'performance_trends' => $performanceTrends,
            'subject_analysis' => $subjectAnalysis,
            'time_analysis' => $timeAnalysis,
            'streak_analysis' => $streakAnalysis,
            'achievement_progress' => $achievements,
            'learning_insights' => $this->generateLearningInsights($user, $basicStats, $subjectAnalysis),
            'goals' => $this->getSuggestedGoals($user, $basicStats)
        ];
    }

    protected function getBasicStats(User $user): array
    {
        $totalQuizzes = UserQuizProgress::where('user_id', $user->id)->count();
        $totalPoints = $user->getTotalPoints();
        $averageScore = UserQuizProgress::where('user_id', $user->id)->avg('score') ?? 0;
        
        $timedQuizzes = UserPoints::where('user_id', $user->id)
            ->where('reason', 'like', '%speed bonus%')
            ->distinct('quiz_id')
            ->count();

        $perfectScores = UserQuizProgress::where('user_id', $user->id)
            ->where('score', 100)
            ->count();

        return [
            'total_quizzes' => $totalQuizzes,
            'total_points' => $totalPoints,
            'average_score' => round($averageScore, 1),
            'timed_quizzes' => $timedQuizzes,
            'perfect_scores' => $perfectScores,
            'completion_rate' => $totalQuizzes > 0 ? round(($perfectScores / $totalQuizzes) * 100, 1) : 0
        ];
    }

    protected function getPerformanceTrends(User $user): array
    {
        $last30Days = UserQuizProgress::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at')
            ->get();

        $weeklyPerformance = [];
        $dailyActivity = [];
        
        // Group by weeks for trend analysis
        for ($week = 4; $week >= 0; $week--) {
            $weekStart = now()->startOfWeek()->subWeeks($week);
            $weekEnd = $weekStart->copy()->endOfWeek();
            
            $weekQuizzes = $last30Days->whereBetween('created_at', [$weekStart, $weekEnd]);
            $weeklyPerformance[] = [
                'week' => $weekStart->format('M d'),
                'quizzes' => $weekQuizzes->count(),
                'average_score' => $weekQuizzes->avg('score') ?? 0,
                'points_earned' => UserPoints::where('user_id', $user->id)
                    ->whereBetween('earned_at', [$weekStart, $weekEnd])
                    ->sum('points') ?? 0
            ];
        }

        // Daily activity for last 14 days
        for ($day = 13; $day >= 0; $day--) {
            $date = now()->subDays($day)->startOfDay();
            $nextDay = $date->copy()->endOfDay();
            
            $dayQuizzes = UserQuizProgress::where('user_id', $user->id)
                ->whereBetween('created_at', [$date, $nextDay])
                ->count();
            
            $dailyActivity[] = [
                'date' => $date->format('M d'),
                'quizzes' => $dayQuizzes,
                'active' => $dayQuizzes > 0
            ];
        }

        return [
            'weekly_performance' => $weeklyPerformance,
            'daily_activity' => $dailyActivity
        ];
    }

    protected function getSubjectAnalysis(User $user): array
    {
        $quizProgress = UserQuizProgress::where('user_id', $user->id)->get();
        $subjectStats = [];
        
        // Map section IDs to subject names
        $subjectMapping = [
            'foundations' => 'Grammar Foundations',
            'daily-life' => 'Daily Life',
            'city' => 'City & Prepositions', 
            'restaurant' => 'Restaurant & Quantifiers',
            'questions' => 'Wh-Questions',
            'quiz-1' => 'Basic Grammar',
            'quiz-2' => 'Daily Life Practice',
            'quiz-3' => 'City Navigation',
            'quiz-4' => 'Restaurant Vocabulary',
            'quiz-5' => 'Question Formation'
        ];

        foreach ($quizProgress as $progress) {
            $subject = $subjectMapping[$progress->section_id] ?? 'Other';
            
            if (!isset($subjectStats[$subject])) {
                $subjectStats[$subject] = [
                    'attempts' => 0,
                    'total_score' => 0,
                    'best_score' => 0,
                    'perfect_scores' => 0
                ];
            }

            $subjectStats[$subject]['attempts']++;
            $subjectStats[$subject]['total_score'] += $progress->score;
            $subjectStats[$subject]['best_score'] = max($subjectStats[$subject]['best_score'], $progress->score);
            
            if ($progress->score == 100) {
                $subjectStats[$subject]['perfect_scores']++;
            }
        }

        // Calculate averages and format for frontend
        $formattedStats = [];
        foreach ($subjectStats as $subject => $stats) {
            $formattedStats[] = [
                'subject' => $subject,
                'attempts' => $stats['attempts'],
                'average_score' => round($stats['total_score'] / $stats['attempts'], 1),
                'best_score' => $stats['best_score'],
                'perfect_scores' => $stats['perfect_scores'],
                'mastery_level' => $this->calculateMasteryLevel($stats)
            ];
        }

        return $formattedStats;
    }

    protected function getTimeAnalysis(User $user): array
    {
        $timedQuizzes = UserPoints::where('user_id', $user->id)
            ->where('reason', 'like', '%speed bonus%')
            ->get();

        $speedBonusTotal = $timedQuizzes->sum('points') - UserPoints::where('user_id', $user->id)
            ->where('reason', 'like', '%speed bonus%')
            ->get()
            ->map(function ($point) {
                // Extract base points (subtract speed bonus)
                preg_match('/\(([^)]+)\)/', $point->reason, $matches);
                return isset($matches[1]) ? 0 : $point->points; // Simplified logic
            })->sum();

        return [
            'timed_quizzes_completed' => $timedQuizzes->count(),
            'speed_bonus_earned' => max(0, $speedBonusTotal),
            'average_speed_bonus' => $timedQuizzes->count() > 0 ? 
                round($speedBonusTotal / $timedQuizzes->count(), 1) : 0
        ];
    }

    protected function getStreakAnalysis(User $user): array
    {
        $streak = $user->userStreak;
        $recentActivity = UserQuizProgress::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at')
            ->get()
            ->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            });

        return [
            'current_streak' => $streak ? $streak->current_streak : 0,
            'longest_streak' => $streak ? $streak->longest_streak : 0,
            'active_days_month' => $recentActivity->count(),
            'consistency_score' => min(100, ($recentActivity->count() / 30) * 100)
        ];
    }

    protected function getAchievementProgress(User $user): array
    {
        $totalAchievements = \App\Models\Achievement::where('is_active', true)->count();
        $earnedAchievements = $user->achievements()->count();
        
        return [
            'total_available' => $totalAchievements,
            'earned' => $earnedAchievements,
            'completion_percentage' => $totalAchievements > 0 ? 
                round(($earnedAchievements / $totalAchievements) * 100, 1) : 0
        ];
    }

    protected function generateLearningInsights(User $user, array $basicStats, array $subjectAnalysis): array
    {
        $insights = [];

        // Performance insights
        if ($basicStats['average_score'] >= 85) {
            $insights[] = [
                'type' => 'success',
                'title' => '🌟 Excellent Performance',
                'message' => 'Your average score is ' . $basicStats['average_score'] . '%. You\'re mastering English concepts!'
            ];
        } elseif ($basicStats['average_score'] >= 70) {
            $insights[] = [
                'type' => 'info',
                'title' => '📈 Good Progress',
                'message' => 'You\'re doing well with an average of ' . $basicStats['average_score'] . '%. Keep practicing to reach excellence!'
            ];
        } else {
            $insights[] = [
                'type' => 'warning',
                'title' => '💪 Room for Improvement',
                'message' => 'Focus on understanding concepts better. Review incorrect answers and practice more!'
            ];
        }

        // Subject-specific insights
        if (!empty($subjectAnalysis)) {
            $strongestSubject = collect($subjectAnalysis)->sortByDesc('average_score')->first();
            $weakestSubject = collect($subjectAnalysis)->sortBy('average_score')->first();

            if ($strongestSubject && $strongestSubject['average_score'] > 80) {
                $insights[] = [
                    'type' => 'success',
                    'title' => '🎯 Subject Strength',
                    'message' => 'You excel at ' . $strongestSubject['subject'] . ' with ' . $strongestSubject['average_score'] . '% average!'
                ];
            }

            if ($weakestSubject && $weakestSubject['average_score'] < 70) {
                $insights[] = [
                    'type' => 'tip',
                    'title' => '📚 Focus Area',
                    'message' => 'Consider spending more time on ' . $weakestSubject['subject'] . ' to improve your overall performance.'
                ];
            }
        }

        // Timed quiz insights
        if ($basicStats['timed_quizzes'] > 0) {
            $insights[] = [
                'type' => 'info',
                'title' => '⚡ Speed Challenge',
                'message' => 'You\'ve completed ' . $basicStats['timed_quizzes'] . ' timed quizzes. Great job handling pressure!'
            ];
        } else {
            $insights[] = [
                'type' => 'tip',
                'title' => '🕐 Try Timed Quizzes',
                'message' => 'Challenge yourself with timed quizzes to earn speed bonus points and new achievements!'
            ];
        }

        return $insights;
    }

    protected function getSuggestedGoals(User $user, array $basicStats): array
    {
        $goals = [];

        // Score-based goals
        if ($basicStats['average_score'] < 85) {
            $targetScore = min(100, $basicStats['average_score'] + 15);
            $goals[] = [
                'type' => 'score',
                'title' => 'Improve Average Score',
                'description' => 'Reach ' . $targetScore . '% average score',
                'current' => $basicStats['average_score'],
                'target' => $targetScore,
                'progress' => min(100, ($basicStats['average_score'] / $targetScore) * 100)
            ];
        }

        // Quiz completion goals
        $nextMilestone = $this->getNextQuizMilestone($basicStats['total_quizzes']);
        if ($nextMilestone > $basicStats['total_quizzes']) {
            $goals[] = [
                'type' => 'completion',
                'title' => 'Quiz Completion',
                'description' => 'Complete ' . $nextMilestone . ' quizzes',
                'current' => $basicStats['total_quizzes'],
                'target' => $nextMilestone,
                'progress' => ($basicStats['total_quizzes'] / $nextMilestone) * 100
            ];
        }

        // Perfect score goals
        if ($basicStats['perfect_scores'] < 5) {
            $goals[] = [
                'type' => 'perfectscore',
                'title' => 'Perfect Scores',
                'description' => 'Achieve 5 perfect scores (100%)',
                'current' => $basicStats['perfect_scores'],
                'target' => 5,
                'progress' => ($basicStats['perfect_scores'] / 5) * 100
            ];
        }

        return $goals;
    }

    protected function calculateMasteryLevel(array $stats): string
    {
        $averageScore = $stats['total_score'] / $stats['attempts'];
        $perfectRate = $stats['perfect_scores'] / $stats['attempts'];

        if ($averageScore >= 90 && $perfectRate >= 0.5) {
            return 'Expert';
        } elseif ($averageScore >= 80 && $perfectRate >= 0.3) {
            return 'Advanced';
        } elseif ($averageScore >= 70) {
            return 'Intermediate';
        } elseif ($averageScore >= 60) {
            return 'Beginner';
        } else {
            return 'Learning';
        }
    }

    protected function getNextQuizMilestone(int $current): int
    {
        $milestones = [10, 25, 50, 75, 100, 150, 200, 300, 500];
        
        foreach ($milestones as $milestone) {
            if ($milestone > $current) {
                return $milestone;
            }
        }
        
        return $current + 100; // Default next milestone
    }
}