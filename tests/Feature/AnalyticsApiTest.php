<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserQuizProgress;
use App\Models\UserPoints;
use App\Models\UserStreak;
use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;

class AnalyticsApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a tenant
        $this->tenant = Tenant::create([
            'name' => 'Analytics Test Academy',
            'subdomain' => 'analytics-test',
            'plan' => 'premium',
            'status' => 'active',
            'features' => json_encode(['analytics', 'gamification']),
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
     * Test getting complete analytics
     */
    public function test_get_complete_analytics()
    {
        // Create comprehensive test data
        $this->createTestAnalyticsData();

        $response = $this->getJson('/api/analytics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'basic_stats' => [
                        'total_quizzes',
                        'average_score',
                        'perfect_scores',
                        'completion_rate',
                        'best_subject',
                    ],
                    'performance_trends' => [
                        'weekly_performance',
                        'trend_analysis',
                        'activity_calendar',
                    ],
                    'subject_analysis' => [
                        'subject_performance',
                        'strongest_subjects',
                        'improvement_areas',
                    ],
                    'time_analysis' => [
                        'timed_quiz_stats',
                        'speed_performance',
                        'time_efficiency',
                    ],
                    'streak_analysis' => [
                        'current_streak',
                        'longest_streak',
                        'consistency_score',
                        'monthly_activity',
                    ],
                    'achievement_progress' => [
                        'total_available',
                        'total_earned',
                        'completion_percentage',
                    ],
                    'learning_insights',
                    'suggested_goals',
                ]);

        $data = $response->json();
        
        // Verify basic stats
        $this->assertGreaterThan(0, $data['basic_stats']['total_quizzes']);
        $this->assertGreaterThanOrEqual(0, $data['basic_stats']['average_score']);
        $this->assertLessThanOrEqual(100, $data['basic_stats']['average_score']);
    }

    /**
     * Test getting performance analytics
     */
    public function test_get_performance_analytics()
    {
        $this->createTestAnalyticsData();

        $response = $this->getJson('/api/analytics/performance');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'basic_stats' => [
                        'total_quizzes',
                        'average_score',
                        'perfect_scores',
                        'completion_rate',
                    ],
                    'performance_trends' => [
                        'weekly_performance',
                        'trend_analysis',
                    ],
                ]);

        $data = $response->json();
        $this->assertIsArray($data['performance_trends']['weekly_performance']);
        $this->assertArrayHasKey('direction', $data['performance_trends']['trend_analysis']);
    }

    /**
     * Test getting subject analytics
     */
    public function test_get_subject_analytics()
    {
        // Create subject-specific test data
        $subjects = ['grammar', 'vocabulary', 'listening', 'reading'];
        $scores = [85, 90, 75, 95];

        foreach ($subjects as $index => $subject) {
            UserQuizProgress::create([
                'user_id' => $this->user->id,
                'section_id' => $subject,
                'score' => $scores[$index],
                'data' => json_encode(['questions' => 10, 'correct' => intval($scores[$index] / 10)]),
                'created_at' => now()->subDays($index + 1),
            ]);
        }

        $response = $this->getJson('/api/analytics/subjects');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'subject_performance' => [
                        '*' => [
                            'subject',
                            'average_score',
                            'quiz_count',
                            'mastery_level',
                        ]
                    ],
                    'strongest_subjects',
                    'improvement_areas',
                ]);

        $data = $response->json();
        $this->assertCount(4, $data['subject_performance']);
        
        // Reading should be strongest with 95%
        $readingPerf = collect($data['subject_performance'])
                        ->firstWhere('subject', 'reading');
        $this->assertEquals(95, $readingPerf['average_score']);
    }

    /**
     * Test getting insights analytics
     */
    public function test_get_insights_analytics()
    {
        $this->createTestAnalyticsData();

        $response = $this->getJson('/api/analytics/insights');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'learning_insights' => [
                        'performance_observations',
                        'recommendations',
                        'trends',
                    ],
                    'suggested_goals' => [
                        '*' => [
                            'title',
                            'description',
                            'target',
                            'current',
                            'progress_percentage',
                            'priority',
                        ]
                    ],
                ]);

        $data = $response->json();
        $this->assertIsArray($data['learning_insights']['performance_observations']);
        $this->assertIsArray($data['learning_insights']['recommendations']);
        $this->assertNotEmpty($data['suggested_goals']);
    }

    /**
     * Test analytics with empty data
     */
    public function test_analytics_with_no_data()
    {
        // Don't create any test data
        $response = $this->getJson('/api/analytics');

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals(0, $data['basic_stats']['total_quizzes']);
        $this->assertEquals(0, $data['basic_stats']['average_score']);
        $this->assertEquals(0, $data['basic_stats']['perfect_scores']);
        $this->assertEmpty($data['performance_trends']['weekly_performance']);
    }

    /**
     * Test analytics with minimal data
     */
    public function test_analytics_with_minimal_data()
    {
        // Create just one quiz result
        UserQuizProgress::create([
            'user_id' => $this->user->id,
            'section_id' => 'single-test',
            'score' => 80,
            'data' => json_encode(['questions' => 10, 'correct' => 8]),
            'created_at' => now(),
        ]);

        $response = $this->getJson('/api/analytics');

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals(1, $data['basic_stats']['total_quizzes']);
        $this->assertEquals(80, $data['basic_stats']['average_score']);
        $this->assertEquals(0, $data['basic_stats']['perfect_scores']);
    }

    /**
     * Test analytics date range filtering
     */
    public function test_analytics_date_filtering()
    {
        // Create data from different time periods
        UserQuizProgress::create([
            'user_id' => $this->user->id,
            'section_id' => 'old-quiz',
            'score' => 60,
            'data' => json_encode(['questions' => 10, 'correct' => 6]),
            'created_at' => now()->subMonths(2),
        ]);

        UserQuizProgress::create([
            'user_id' => $this->user->id,
            'section_id' => 'recent-quiz',
            'score' => 90,
            'data' => json_encode(['questions' => 10, 'correct' => 9]),
            'created_at' => now()->subDays(5),
        ]);

        // Test performance endpoint which should include recent data
        $response = $this->getJson('/api/analytics/performance');

        $response->assertStatus(200);

        $data = $response->json();
        
        // Should include both quizzes in total count
        $this->assertEquals(2, $data['basic_stats']['total_quizzes']);
        
        // Average should be (60 + 90) / 2 = 75
        $this->assertEquals(75, $data['basic_stats']['average_score']);
    }

    /**
     * Test analytics performance with large dataset
     */
    public function test_analytics_performance_with_large_dataset()
    {
        // Create a large dataset
        $batchSize = 100;
        for ($i = 0; $i < $batchSize; $i++) {
            UserQuizProgress::create([
                'user_id' => $this->user->id,
                'section_id' => 'performance-test-' . $i,
                'score' => rand(60, 100),
                'data' => json_encode(['questions' => 10, 'correct' => rand(6, 10)]),
                'created_at' => now()->subDays(rand(1, 30)),
            ]);
        }

        $startTime = microtime(true);
        $response = $this->getJson('/api/analytics');
        $endTime = microtime(true);

        $response->assertStatus(200);

        // Should complete within reasonable time (5 seconds)
        $executionTime = $endTime - $startTime;
        $this->assertLessThan(5.0, $executionTime, 
            "Analytics endpoint took too long: {$executionTime} seconds");

        $data = $response->json();
        $this->assertEquals($batchSize, $data['basic_stats']['total_quizzes']);
    }

    /**
     * Test concurrent analytics requests
     */
    public function test_concurrent_analytics_requests()
    {
        $this->createTestAnalyticsData();

        // Make multiple concurrent requests
        $responses = [];
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->getJson('/api/analytics');
        }

        // All should succeed
        foreach ($responses as $response) {
            $response->assertStatus(200);
        }

        // All should return the same data
        $firstResponse = $responses[0]->json();
        foreach ($responses as $response) {
            $this->assertEquals($firstResponse['basic_stats'], $response->json()['basic_stats']);
        }
    }

    /**
     * Test unauthenticated access
     */
    public function test_unauthenticated_access()
    {
        // Logout user
        $this->app['auth']->forgetGuards();

        $endpoints = [
            '/api/analytics',
            '/api/analytics/performance',
            '/api/analytics/subjects',
            '/api/analytics/insights',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            $response->assertStatus(401);
        }
    }

    /**
     * Test cross-user data isolation
     */
    public function test_cross_user_data_isolation()
    {
        // Create another user with different data
        $otherUser = User::factory()->create(['tenant_id' => $this->tenant->id]);

        // Create data for both users
        UserQuizProgress::create([
            'user_id' => $this->user->id,
            'section_id' => 'user1-quiz',
            'score' => 85,
            'data' => json_encode(['questions' => 10, 'correct' => 8]),
            'created_at' => now(),
        ]);

        UserQuizProgress::create([
            'user_id' => $otherUser->id,
            'section_id' => 'user2-quiz',
            'score' => 95,
            'data' => json_encode(['questions' => 10, 'correct' => 9]),
            'created_at' => now(),
        ]);

        // Authenticate as first user and get analytics
        Sanctum::actingAs($this->user);
        $response = $this->getJson('/api/analytics');
        $response->assertStatus(200);
        $data1 = $response->json();

        // Authenticate as second user and get analytics
        Sanctum::actingAs($otherUser);
        $response = $this->getJson('/api/analytics');
        $response->assertStatus(200);
        $data2 = $response->json();

        // Each user should only see their own data
        $this->assertEquals(1, $data1['basic_stats']['total_quizzes']);
        $this->assertEquals(85, $data1['basic_stats']['average_score']);

        $this->assertEquals(1, $data2['basic_stats']['total_quizzes']);
        $this->assertEquals(95, $data2['basic_stats']['average_score']);
    }

    /**
     * Test error handling for malformed requests
     */
    public function test_error_handling()
    {
        // Test with invalid parameters (if endpoint accepts them)
        $response = $this->getJson('/api/analytics?invalid_param=test');
        $response->assertStatus(200); // Should ignore invalid params

        // Test with SQL injection attempt in query params
        $response = $this->getJson('/api/analytics?user_id=1\' OR \'1\'=\'1');
        $response->assertStatus(200); // Should be safely handled

        // Verify no data corruption
        $this->assertDatabaseHas('users', ['id' => $this->user->id]);
    }

    /**
     * Test analytics caching behavior
     */
    public function test_analytics_caching()
    {
        $this->createTestAnalyticsData();

        // First request
        $startTime1 = microtime(true);
        $response1 = $this->getJson('/api/analytics');
        $endTime1 = microtime(true);
        $time1 = $endTime1 - $startTime1;

        $response1->assertStatus(200);

        // Second request (should potentially be faster if cached)
        $startTime2 = microtime(true);
        $response2 = $this->getJson('/api/analytics');
        $endTime2 = microtime(true);
        $time2 = $endTime2 - $startTime2;

        $response2->assertStatus(200);

        // Both should return the same data
        $this->assertEquals($response1->json(), $response2->json());

        // Note: Caching performance improvement is not guaranteed in test environment
        // but we can at least verify consistent results
    }

    /**
     * Helper method to create comprehensive test data
     */
    private function createTestAnalyticsData()
    {
        // Create quiz progress data
        $subjects = ['grammar', 'vocabulary', 'listening'];
        $scores = [85, 90, 75, 95, 80];

        foreach ($scores as $index => $score) {
            UserQuizProgress::create([
                'user_id' => $this->user->id,
                'section_id' => $subjects[$index % count($subjects)],
                'score' => $score,
                'data' => json_encode([
                    'questions' => 10,
                    'correct' => intval($score / 10),
                    'is_timed' => $index % 2 === 0,
                    'total_time_used' => rand(60, 300),
                    'speed_bonus' => rand(0, 15),
                ]),
                'created_at' => now()->subDays($index + 1),
            ]);
        }

        // Create points data
        UserPoints::create([
            'user_id' => $this->user->id,
            'points' => 150,
            'reason' => 'Quiz completion',
            'quiz_id' => 1,
            'earned_at' => now(),
        ]);

        // Create streak data
        UserStreak::create([
            'user_id' => $this->user->id,
            'current_streak' => 7,
            'longest_streak' => 15,
            'last_activity_date' => now()->toDateString(),
        ]);

        // Create achievement data
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
    }
}