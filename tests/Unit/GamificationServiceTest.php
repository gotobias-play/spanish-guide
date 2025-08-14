<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\GamificationService;
use App\Models\User;
use App\Models\UserPoints;
use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Models\UserStreak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class GamificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GamificationService $gamificationService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gamificationService = new GamificationService();
        
        // Create a test user
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    /**
     * Test point calculation for quiz completion
     */
    public function test_calculate_quiz_points()
    {
        // Test perfect score (100%)
        $points = $this->gamificationService->calculateQuizPoints(100, false, 0);
        $this->assertEquals(50, $points);

        // Test good score (85%)
        $points = $this->gamificationService->calculateQuizPoints(85, false, 0);
        $this->assertEquals(30, $points);

        // Test passing score (70%)
        $points = $this->gamificationService->calculateQuizPoints(70, false, 0);
        $this->assertEquals(20, $points);

        // Test failing score (40%)
        $points = $this->gamificationService->calculateQuizPoints(40, false, 0);
        $this->assertEquals(10, $points);

        // Test with speed bonus
        $points = $this->gamificationService->calculateQuizPoints(100, true, 15);
        $this->assertEquals(65, $points); // 50 base + 15 speed bonus
    }

    /**
     * Test awarding points to user
     */
    public function test_award_points_to_user()
    {
        $points = 50;
        $reason = 'Quiz completion';
        $quizId = 1;

        $result = $this->gamificationService->awardPoints(
            $this->user->id,
            $points,
            $reason,
            $quizId
        );

        $this->assertTrue($result);

        // Check if points were recorded
        $userPoints = UserPoints::where('user_id', $this->user->id)->first();
        $this->assertNotNull($userPoints);
        $this->assertEquals($points, $userPoints->points);
        $this->assertEquals($reason, $userPoints->reason);
        $this->assertEquals($quizId, $userPoints->quiz_id);
    }

    /**
     * Test achievement checking and awarding
     */
    public function test_check_and_award_achievements()
    {
        // Create a test achievement
        $achievement = Achievement::create([
            'name' => 'First Steps',
            'description' => 'Complete your first quiz',
            'badge_icon' => '🌟',
            'badge_color' => '#FFD700',
            'points_required' => 0,
            'condition_type' => 'quiz_count',
            'condition_value' => json_encode(['count' => 1]),
            'is_active' => true,
        ]);

        // Award points for quiz completion (which should trigger achievement check)
        $this->gamificationService->awardPoints($this->user->id, 50, 'Quiz completion', 1);

        // Check if achievement was awarded
        $userAchievement = UserAchievement::where('user_id', $this->user->id)
                                        ->where('achievement_id', $achievement->id)
                                        ->first();
        
        $this->assertNotNull($userAchievement, 'First Steps achievement should be awarded');
    }

    /**
     * Test streak management
     */
    public function test_streak_management()
    {
        // Test initial streak creation
        $this->gamificationService->updateStreak($this->user->id);

        $streak = UserStreak::where('user_id', $this->user->id)->first();
        $this->assertNotNull($streak);
        $this->assertEquals(1, $streak->current_streak);
        $this->assertEquals(1, $streak->longest_streak);

        // Test consecutive day streak
        $streak->last_activity_date = now()->subDay()->toDateString();
        $streak->save();

        $this->gamificationService->updateStreak($this->user->id);
        $streak->refresh();

        $this->assertEquals(2, $streak->current_streak);
        $this->assertEquals(2, $streak->longest_streak);

        // Test streak break (gap of more than 1 day)
        $streak->last_activity_date = now()->subDays(3)->toDateString();
        $streak->save();

        $this->gamificationService->updateStreak($this->user->id);
        $streak->refresh();

        $this->assertEquals(1, $streak->current_streak); // Reset to 1
        $this->assertEquals(2, $streak->longest_streak); // Longest streak preserved
    }

    /**
     * Test getting user statistics
     */
    public function test_get_user_statistics()
    {
        // Setup test data
        UserPoints::create([
            'user_id' => $this->user->id,
            'points' => 50,
            'reason' => 'Quiz 1',
            'quiz_id' => 1,
            'earned_at' => now(),
        ]);

        UserPoints::create([
            'user_id' => $this->user->id,
            'points' => 30,
            'reason' => 'Quiz 2',
            'quiz_id' => 2,
            'earned_at' => now(),
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

        UserStreak::create([
            'user_id' => $this->user->id,
            'current_streak' => 5,
            'longest_streak' => 10,
            'last_activity_date' => now()->toDateString(),
        ]);

        // Get statistics
        $stats = $this->gamificationService->getUserStats($this->user->id);

        $this->assertEquals(80, $stats['total_points']); // 50 + 30
        $this->assertEquals(2, $stats['total_quizzes']);
        $this->assertEquals(1, $stats['total_achievements']);
        $this->assertEquals(5, $stats['current_streak']);
        $this->assertEquals(10, $stats['longest_streak']);
    }

    /**
     * Test points history retrieval
     */
    public function test_get_points_history()
    {
        // Create test points history
        UserPoints::create([
            'user_id' => $this->user->id,
            'points' => 50,
            'reason' => 'Grammar Quiz',
            'quiz_id' => 1,
            'earned_at' => now()->subDays(2),
        ]);

        UserPoints::create([
            'user_id' => $this->user->id,
            'points' => 30,
            'reason' => 'Vocabulary Quiz',
            'quiz_id' => 2,
            'earned_at' => now()->subDay(),
        ]);

        UserPoints::create([
            'user_id' => $this->user->id,
            'points' => 45,
            'reason' => 'Timed Quiz Bonus',
            'quiz_id' => 3,
            'earned_at' => now(),
        ]);

        // Get points history
        $history = $this->gamificationService->getPointsHistory($this->user->id);

        $this->assertCount(3, $history);
        
        // Check if sorted by most recent first
        $this->assertEquals(45, $history[0]['points']);
        $this->assertEquals('Timed Quiz Bonus', $history[0]['reason']);
        
        $this->assertEquals(30, $history[1]['points']);
        $this->assertEquals('Vocabulary Quiz', $history[1]['reason']);
        
        $this->assertEquals(50, $history[2]['points']);
        $this->assertEquals('Grammar Quiz', $history[2]['reason']);
    }

    /**
     * Test leaderboard functionality
     */
    public function test_leaderboard()
    {
        // Create additional test users
        $user2 = User::factory()->create(['name' => 'User 2']);
        $user3 = User::factory()->create(['name' => 'User 3']);

        // Award different amounts of points
        UserPoints::create([
            'user_id' => $this->user->id,
            'points' => 100,
            'reason' => 'Quiz completion',
            'earned_at' => now(),
        ]);

        UserPoints::create([
            'user_id' => $user2->id,
            'points' => 150,
            'reason' => 'Quiz completion',
            'earned_at' => now(),
        ]);

        UserPoints::create([
            'user_id' => $user3->id,
            'points' => 75,
            'reason' => 'Quiz completion',
            'earned_at' => now(),
        ]);

        // Get leaderboard
        $leaderboard = $this->gamificationService->getLeaderboard();

        $this->assertCount(3, $leaderboard);
        
        // Check if sorted by points (highest first)
        $this->assertEquals($user2->id, $leaderboard[0]['user_id']);
        $this->assertEquals(150, $leaderboard[0]['total_points']);
        
        $this->assertEquals($this->user->id, $leaderboard[1]['user_id']);
        $this->assertEquals(100, $leaderboard[1]['total_points']);
        
        $this->assertEquals($user3->id, $leaderboard[2]['user_id']);
        $this->assertEquals(75, $leaderboard[2]['total_points']);
    }

    /**
     * Test edge cases and error handling
     */
    public function test_edge_cases()
    {
        // Test with invalid user ID
        $result = $this->gamificationService->awardPoints(99999, 50, 'Test', 1);
        $this->assertFalse($result);

        // Test with negative points
        $result = $this->gamificationService->awardPoints($this->user->id, -10, 'Test', 1);
        $this->assertFalse($result);

        // Test with zero points
        $result = $this->gamificationService->awardPoints($this->user->id, 0, 'Test', 1);
        $this->assertTrue($result); // Zero points should be allowed

        // Test empty statistics for user with no data
        $newUser = User::factory()->create();
        $stats = $this->gamificationService->getUserStats($newUser->id);
        
        $this->assertEquals(0, $stats['total_points']);
        $this->assertEquals(0, $stats['total_quizzes']);
        $this->assertEquals(0, $stats['total_achievements']);
        $this->assertEquals(0, $stats['current_streak']);
        $this->assertEquals(0, $stats['longest_streak']);
    }
}