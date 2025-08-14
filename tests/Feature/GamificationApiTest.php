<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserPoints;
use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Models\UserStreak;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class GamificationApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a tenant
        $this->tenant = Tenant::create([
            'name' => 'Test Academy',
            'subdomain' => 'test-academy',
            'plan' => 'premium',
            'status' => 'active',
            'features' => json_encode(['gamification', 'analytics']),
            'limits' => json_encode(['users' => 200, 'courses' => 50]),
            'usage' => json_encode(['users' => 0, 'courses' => 0]),
            'branding' => json_encode([]),
            'billing_info' => json_encode([]),
            'locale' => 'en',
            'timezone' => 'UTC',
        ]);

        // Create and authenticate a user
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        
        Sanctum::actingAs($this->user);
    }

    /**
     * Test awarding points for quiz completion
     */
    public function test_award_quiz_points()
    {
        $quizData = [
            'section_id' => 'grammar-test',
            'score' => 85,
            'quiz_id' => 1,
            'is_timed' => false,
            'speed_bonus' => 0,
        ];

        $response = $this->postJson('/api/gamification/quiz-points', $quizData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'points_awarded' => 30, // 85% should award 30 points
                ]);

        // Verify points were recorded in database
        $this->assertDatabaseHas('user_points', [
            'user_id' => $this->user->id,
            'points' => 30,
            'quiz_id' => 1,
        ]);
    }

    /**
     * Test awarding points with speed bonus
     */
    public function test_award_quiz_points_with_speed_bonus()
    {
        $quizData = [
            'section_id' => 'grammar-test',
            'score' => 100,
            'quiz_id' => 2,
            'is_timed' => true,
            'speed_bonus' => 15,
        ];

        $response = $this->postJson('/api/gamification/quiz-points', $quizData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'points_awarded' => 65, // 50 base + 15 speed bonus
                ]);

        // Check if achievement was triggered
        $response->assertJsonStructure([
            'success',
            'points_awarded',
            'achievements_unlocked' => ['*' => ['name', 'description', 'badge_icon']],
        ]);
    }

    /**
     * Test getting user gamification statistics
     */
    public function test_get_user_stats()
    {
        // Create test data
        UserPoints::create([
            'user_id' => $this->user->id,
            'points' => 50,
            'reason' => 'Grammar quiz',
            'quiz_id' => 1,
            'earned_at' => now(),
        ]);

        UserStreak::create([
            'user_id' => $this->user->id,
            'current_streak' => 5,
            'longest_streak' => 10,
            'last_activity_date' => now()->toDateString(),
        ]);

        $response = $this->getJson('/api/gamification/stats');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'total_points',
                    'total_quizzes', 
                    'total_achievements',
                    'current_streak',
                    'longest_streak',
                    'average_score',
                ]);

        $data = $response->json();
        $this->assertEquals(50, $data['total_points']);
        $this->assertEquals(1, $data['total_quizzes']);
        $this->assertEquals(5, $data['current_streak']);
    }

    /**
     * Test getting user achievements
     */
    public function test_get_user_achievements()
    {
        // Create an achievement
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

        // Award achievement to user
        UserAchievement::create([
            'user_id' => $this->user->id,
            'achievement_id' => $achievement->id,
            'earned_at' => now(),
        ]);

        $response = $this->getJson('/api/gamification/achievements');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'badge_icon',
                        'badge_color',
                        'earned_at',
                    ]
                ]);

        $achievements = $response->json();
        $this->assertCount(1, $achievements);
        $this->assertEquals('First Steps', $achievements[0]['name']);
    }

    /**
     * Test getting points history
     */
    public function test_get_points_history()
    {
        // Create points history
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

        $response = $this->getJson('/api/gamification/points-history');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    '*' => [
                        'points',
                        'reason',
                        'quiz_id',
                        'earned_at',
                    ]
                ]);

        $history = $response->json();
        $this->assertCount(2, $history);
        
        // Should be ordered by most recent first
        $this->assertEquals(30, $history[0]['points']);
        $this->assertEquals('Vocabulary Quiz', $history[0]['reason']);
    }

    /**
     * Test getting public achievements list
     */
    public function test_get_public_achievements()
    {
        // Create test achievements
        Achievement::create([
            'name' => 'Quiz Master',
            'description' => 'Complete 10 quizzes',
            'badge_icon' => '🎓',
            'badge_color' => '#4169E1',
            'points_required' => 0,
            'condition_type' => 'quiz_count',
            'condition_value' => json_encode(['count' => 10]),
            'is_active' => true,
        ]);

        Achievement::create([
            'name' => 'Perfect Score',
            'description' => 'Get 100% on any quiz',
            'badge_icon' => '💯',
            'badge_color' => '#32CD32',
            'points_required' => 0,
            'condition_type' => 'perfect_score',
            'condition_value' => json_encode(['required' => true]),
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/public/achievements');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'badge_icon',
                        'badge_color',
                    ]
                ]);

        $achievements = $response->json();
        $this->assertCount(2, $achievements);
    }

    /**
     * Test getting public leaderboard
     */
    public function test_get_public_leaderboard()
    {
        // Create additional users with points
        $user2 = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $user3 = User::factory()->create(['tenant_id' => $this->tenant->id]);

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

        $response = $this->getJson('/api/public/leaderboard');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    '*' => [
                        'user_id',
                        'user_name',
                        'total_points',
                        'total_quizzes',
                        'total_achievements',
                        'rank',
                    ]
                ]);

        $leaderboard = $response->json();
        $this->assertCount(3, $leaderboard);
        
        // Should be ordered by points (highest first)
        $this->assertEquals(150, $leaderboard[0]['total_points']);
        $this->assertEquals(100, $leaderboard[1]['total_points']);
        $this->assertEquals(75, $leaderboard[2]['total_points']);
        
        // Check rankings
        $this->assertEquals(1, $leaderboard[0]['rank']);
        $this->assertEquals(2, $leaderboard[1]['rank']);
        $this->assertEquals(3, $leaderboard[2]['rank']);
    }

    /**
     * Test validation errors
     */
    public function test_validation_errors()
    {
        // Test missing required fields
        $response = $this->postJson('/api/gamification/quiz-points', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['section_id', 'score']);

        // Test invalid score range
        $response = $this->postJson('/api/gamification/quiz-points', [
            'section_id' => 'test',
            'score' => 150, // Invalid: over 100
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['score']);

        // Test negative score
        $response = $this->postJson('/api/gamification/quiz-points', [
            'section_id' => 'test',
            'score' => -10, // Invalid: negative
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['score']);
    }

    /**
     * Test unauthenticated access
     */
    public function test_unauthenticated_access()
    {
        // Logout user
        $this->app['auth']->forgetGuards();

        // Try to access protected endpoints
        $response = $this->postJson('/api/gamification/quiz-points', [
            'section_id' => 'test',
            'score' => 85,
        ]);

        $response->assertStatus(401);

        $response = $this->getJson('/api/gamification/stats');
        $response->assertStatus(401);

        $response = $this->getJson('/api/gamification/achievements');
        $response->assertStatus(401);

        // Public endpoints should still work
        $response = $this->getJson('/api/public/achievements');
        $response->assertStatus(200);

        $response = $this->getJson('/api/public/leaderboard');
        $response->assertStatus(200);
    }

    /**
     * Test concurrent point awarding (race condition)
     */
    public function test_concurrent_point_awarding()
    {
        // Simulate concurrent requests
        $quizData = [
            'section_id' => 'concurrent-test',
            'score' => 85,
            'quiz_id' => 999,
        ];

        // Make multiple concurrent requests
        $responses = [];
        for ($i = 0; $i < 3; $i++) {
            $responses[] = $this->postJson('/api/gamification/quiz-points', $quizData);
        }

        // All should succeed
        foreach ($responses as $response) {
            $response->assertStatus(200);
        }

        // Should have 3 separate point records
        $pointsCount = UserPoints::where('user_id', $this->user->id)
                                ->where('quiz_id', 999)
                                ->count();
        
        $this->assertEquals(3, $pointsCount);
    }

    /**
     * Test error handling for invalid data
     */
    public function test_error_handling()
    {
        // Test with extremely large values
        $response = $this->postJson('/api/gamification/quiz-points', [
            'section_id' => str_repeat('a', 1000), // Very long string
            'score' => 85,
            'quiz_id' => PHP_INT_MAX,
        ]);

        $response->assertStatus(422);

        // Test with SQL injection attempt
        $response = $this->postJson('/api/gamification/quiz-points', [
            'section_id' => "'; DROP TABLE users; --",
            'score' => 85,
            'quiz_id' => 1,
        ]);

        // Should either succeed (safely escaped) or return validation error
        $this->assertContains($response->status(), [200, 422]);
        
        // Database should still be intact
        $this->assertDatabaseHas('users', ['id' => $this->user->id]);
    }
}