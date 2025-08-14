<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AnalyticsService;
use App\Models\User;
use App\Models\UserQuizProgress;
use App\Models\UserPoints;
use App\Models\UserAchievement;
use App\Models\UserStreak;
use App\Models\Achievement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AnalyticsService $analyticsService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyticsService = new AnalyticsService();
        
        // Create a test user
        $this->user = User::factory()->create([
            'name' => 'Analytics Test User',
            'email' => 'analytics@example.com',
        ]);
    }

    /**
     * Test basic statistics calculation
     */
    public function test_get_basic_stats()
    {
        // Create test quiz progress data
        UserQuizProgress::create([
            'user_id' => $this->user->id,
            'section_id' => 'daily-life',
            'score' => 85,
            'data' => json_encode(['questions' => 10, 'correct' => 8]),
            'created_at' => now()->subDays(2),
        ]);

        UserQuizProgress::create([
            'user_id' => $this->user->id,
            'section_id' => 'grammar',
            'score' => 95,
            'data' => json_encode(['questions' => 10, 'correct' => 9]),
            'created_at' => now()->subDay(),
        ]);

        UserQuizProgress::create([
            'user_id' => $this->user->id,
            'section_id' => 'vocabulary',
            'score' => 100,
            'data' => json_encode(['questions' => 10, 'correct' => 10]),
            'created_at' => now(),
        ]);

        $basicStats = $this->analyticsService->getBasicStats($this->user->id);

        $this->assertEquals(3, $basicStats['total_quizzes']);
        $this->assertEquals(93.33, round($basicStats['average_score'], 2)); // (85+95+100)/3
        $this->assertEquals(1, $basicStats['perfect_scores']); // One 100% score
        $this->assertArrayHasKey('best_subject', $basicStats);
        $this->assertArrayHasKey('completion_rate', $basicStats);
    }

    /**
     * Test performance trends analysis
     */
    public function test_get_performance_trends()
    {
        // Create test data for multiple weeks
        $dates = [
            now()->subWeeks(4),
            now()->subWeeks(3),
            now()->subWeeks(2),
            now()->subWeeks(1),
            now(),
        ];

        $scores = [70, 75, 85, 90, 95]; // Improving trend

        foreach ($dates as $index => $date) {
            UserQuizProgress::create([
                'user_id' => $this->user->id,
                'section_id' => 'test-section-' . $index,
                'score' => $scores[$index],
                'data' => json_encode(['questions' => 10]),
                'created_at' => $date,
            ]);
        }

        $trends = $this->analyticsService->getPerformanceTrends($this->user->id);

        $this->assertArrayHasKey('weekly_performance', $trends);
        $this->assertArrayHasKey('trend_analysis', $trends);
        $this->assertArrayHasKey('activity_calendar', $trends);
        
        // Check if trend is improving
        $this->assertEquals('improving', $trends['trend_analysis']['direction']);
        $this->assertGreaterThan(0, $trends['trend_analysis']['change_percentage']);
    }

    /**
     * Test subject analysis
     */
    public function test_get_subject_analysis()
    {
        // Create test data for different subjects
        $subjects = [
            'grammar' => [85, 90, 95], // Good performance
            'vocabulary' => [70, 75, 80], // Moderate performance
            'listening' => [95, 100, 100], // Excellent performance
        ];

        foreach ($subjects as $subject => $scores) {
            foreach ($scores as $score) {
                UserQuizProgress::create([
                    'user_id' => $this->user->id,
                    'section_id' => $subject,
                    'score' => $score,
                    'data' => json_encode(['questions' => 10]),
                    'created_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }

        $subjectAnalysis = $this->analyticsService->getSubjectAnalysis($this->user->id);

        $this->assertArrayHasKey('subject_performance', $subjectAnalysis);
        $this->assertArrayHasKey('strongest_subjects', $subjectAnalysis);
        $this->assertArrayHasKey('improvement_areas', $subjectAnalysis);

        // Check if listening is identified as strongest subject
        $strongestSubjects = $subjectAnalysis['strongest_subjects'];
        $this->assertContains('listening', array_column($strongestSubjects, 'subject'));

        // Check if vocabulary is identified as improvement area
        $improvementAreas = $subjectAnalysis['improvement_areas'];
        $this->assertContains('vocabulary', array_column($improvementAreas, 'subject'));
    }

    /**
     * Test time analysis
     */
    public function test_get_time_analysis()
    {
        // Create quiz progress with different times
        UserQuizProgress::create([
            'user_id' => $this->user->id,
            'section_id' => 'timed-quiz-1',
            'score' => 85,
            'data' => json_encode([
                'is_timed' => true,
                'total_time_used' => 120, // 2 minutes
                'speed_bonus' => 10,
            ]),
            'created_at' => now(),
        ]);

        UserQuizProgress::create([
            'user_id' => $this->user->id,
            'section_id' => 'timed-quiz-2',
            'score' => 90,
            'data' => json_encode([
                'is_timed' => true,
                'total_time_used' => 90, // 1.5 minutes
                'speed_bonus' => 15,
            ]),
            'created_at' => now(),
        ]);

        $timeAnalysis = $this->analyticsService->getTimeAnalysis($this->user->id);

        $this->assertArrayHasKey('timed_quiz_stats', $timeAnalysis);
        $this->assertArrayHasKey('speed_performance', $timeAnalysis);
        $this->assertArrayHasKey('time_efficiency', $timeAnalysis);

        $timedStats = $timeAnalysis['timed_quiz_stats'];
        $this->assertEquals(2, $timedStats['total_timed_quizzes']);
        $this->assertEquals(105, $timedStats['average_time']); // (120+90)/2
        $this->assertEquals(12.5, $timedStats['average_speed_bonus']); // (10+15)/2
    }

    /**
     * Test streak analysis
     */
    public function test_get_streak_analysis()
    {
        // Create user streak data
        UserStreak::create([
            'user_id' => $this->user->id,
            'current_streak' => 7,
            'longest_streak' => 15,
            'last_activity_date' => now()->toDateString(),
        ]);

        // Create some activity for consistency calculation
        for ($i = 0; $i < 20; $i++) {
            UserQuizProgress::create([
                'user_id' => $this->user->id,
                'section_id' => 'consistency-test-' . $i,
                'score' => rand(70, 100),
                'data' => json_encode(['questions' => 10]),
                'created_at' => now()->subDays($i),
            ]);
        }

        $streakAnalysis = $this->analyticsService->getStreakAnalysis($this->user->id);

        $this->assertArrayHasKey('current_streak', $streakAnalysis);
        $this->assertArrayHasKey('longest_streak', $streakAnalysis);
        $this->assertArrayHasKey('consistency_score', $streakAnalysis);
        $this->assertArrayHasKey('monthly_activity', $streakAnalysis);

        $this->assertEquals(7, $streakAnalysis['current_streak']);
        $this->assertEquals(15, $streakAnalysis['longest_streak']);
        $this->assertGreaterThan(0, $streakAnalysis['consistency_score']);
    }

    /**
     * Test achievement progress analysis
     */
    public function test_get_achievement_progress()
    {
        // Create test achievements
        $achievements = [
            [
                'name' => 'First Steps',
                'description' => 'Complete first quiz',
                'badge_icon' => '🌟',
                'badge_color' => '#FFD700',
                'points_required' => 0,
                'condition_type' => 'quiz_count',
                'condition_value' => json_encode(['count' => 1]),
                'is_active' => true,
            ],
            [
                'name' => 'Quiz Master',
                'description' => 'Complete 10 quizzes',
                'badge_icon' => '🎓',
                'badge_color' => '#4169E1',
                'points_required' => 0,
                'condition_type' => 'quiz_count',
                'condition_value' => json_encode(['count' => 10]),
                'is_active' => true,
            ],
        ];

        foreach ($achievements as $achievementData) {
            Achievement::create($achievementData);
        }

        // Award one achievement
        $firstAchievement = Achievement::where('name', 'First Steps')->first();
        UserAchievement::create([
            'user_id' => $this->user->id,
            'achievement_id' => $firstAchievement->id,
            'earned_at' => now(),
        ]);

        $achievementProgress = $this->analyticsService->getAchievementProgress($this->user->id);

        $this->assertArrayHasKey('total_available', $achievementProgress);
        $this->assertArrayHasKey('total_earned', $achievementProgress);
        $this->assertArrayHasKey('completion_percentage', $achievementProgress);

        $this->assertEquals(2, $achievementProgress['total_available']);
        $this->assertEquals(1, $achievementProgress['total_earned']);
        $this->assertEquals(50, $achievementProgress['completion_percentage']);
    }

    /**
     * Test learning insights generation
     */
    public function test_generate_learning_insights()
    {
        // Create varied performance data
        $scores = [60, 70, 85, 90, 95]; // Improving pattern
        foreach ($scores as $index => $score) {
            UserQuizProgress::create([
                'user_id' => $this->user->id,
                'section_id' => 'insight-test-' . $index,
                'score' => $score,
                'data' => json_encode(['questions' => 10]),
                'created_at' => now()->subDays(5 - $index),
            ]);
        }

        $insights = $this->analyticsService->generateLearningInsights($this->user->id);

        $this->assertIsArray($insights);
        $this->assertArrayHasKey('performance_observations', $insights);
        $this->assertArrayHasKey('recommendations', $insights);
        $this->assertArrayHasKey('trends', $insights);

        // Should detect improving trend
        $this->assertStringContainsString('improving', strtolower(json_encode($insights)));
    }

    /**
     * Test suggested goals generation
     */
    public function test_get_suggested_goals()
    {
        // Create some baseline data
        UserQuizProgress::create([
            'user_id' => $this->user->id,
            'section_id' => 'grammar',
            'score' => 80,
            'data' => json_encode(['questions' => 10]),
            'created_at' => now(),
        ]);

        UserStreak::create([
            'user_id' => $this->user->id,
            'current_streak' => 3,
            'longest_streak' => 5,
            'last_activity_date' => now()->toDateString(),
        ]);

        $suggestedGoals = $this->analyticsService->getSuggestedGoals($this->user->id);

        $this->assertIsArray($suggestedGoals);
        $this->assertNotEmpty($suggestedGoals);

        // Check goal structure
        foreach ($suggestedGoals as $goal) {
            $this->assertArrayHasKey('title', $goal);
            $this->assertArrayHasKey('description', $goal);
            $this->assertArrayHasKey('target', $goal);
            $this->assertArrayHasKey('current', $goal);
            $this->assertArrayHasKey('progress_percentage', $goal);
            $this->assertArrayHasKey('priority', $goal);
        }
    }

    /**
     * Test complete analytics compilation
     */
    public function test_get_complete_analytics()
    {
        // Create comprehensive test data
        UserQuizProgress::create([
            'user_id' => $this->user->id,
            'section_id' => 'comprehensive-test',
            'score' => 85,
            'data' => json_encode(['questions' => 10, 'correct' => 8]),
            'created_at' => now(),
        ]);

        UserStreak::create([
            'user_id' => $this->user->id,
            'current_streak' => 5,
            'longest_streak' => 10,
            'last_activity_date' => now()->toDateString(),
        ]);

        $achievement = Achievement::create([
            'name' => 'Test Achievement',
            'description' => 'Test description',
            'badge_icon' => '🏆',
            'badge_color' => '#FFD700',
            'points_required' => 0,
            'condition_type' => 'quiz_count',
            'condition_value' => json_encode(['count' => 1]),
            'is_active' => true,
        ]);

        UserAchievement::create([
            'user_id' => $this->user->id,
            'achievement_id' => $achievement->id,
            'earned_at' => now(),
        ]);

        $completeAnalytics = $this->analyticsService->getCompleteAnalytics($this->user->id);

        // Verify all sections are present
        $this->assertArrayHasKey('basic_stats', $completeAnalytics);
        $this->assertArrayHasKey('performance_trends', $completeAnalytics);
        $this->assertArrayHasKey('subject_analysis', $completeAnalytics);
        $this->assertArrayHasKey('time_analysis', $completeAnalytics);
        $this->assertArrayHasKey('streak_analysis', $completeAnalytics);
        $this->assertArrayHasKey('achievement_progress', $completeAnalytics);
        $this->assertArrayHasKey('learning_insights', $completeAnalytics);
        $this->assertArrayHasKey('suggested_goals', $completeAnalytics);
    }

    /**
     * Test edge cases and error handling
     */
    public function test_edge_cases()
    {
        // Test analytics for user with no data
        $newUser = User::factory()->create();
        $analytics = $this->analyticsService->getCompleteAnalytics($newUser->id);

        $this->assertEquals(0, $analytics['basic_stats']['total_quizzes']);
        $this->assertEquals(0, $analytics['basic_stats']['average_score']);
        $this->assertEmpty($analytics['performance_trends']['weekly_performance']);

        // Test with invalid user ID
        $invalidAnalytics = $this->analyticsService->getCompleteAnalytics(99999);
        $this->assertIsArray($invalidAnalytics);
        $this->assertEquals(0, $invalidAnalytics['basic_stats']['total_quizzes']);
    }
}