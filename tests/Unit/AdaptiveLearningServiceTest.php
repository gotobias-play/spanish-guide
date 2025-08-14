<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AdaptiveLearningService;
use App\Models\User;
use App\Models\UserSkillLevel;
use App\Models\LearningRecommendation;
use App\Models\UserQuizProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdaptiveLearningServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AdaptiveLearningService $adaptiveLearningService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adaptiveLearningService = new AdaptiveLearningService();
        
        // Create a test user
        $this->user = User::factory()->create([
            'name' => 'Adaptive Learning Test User',
            'email' => 'adaptive@example.com',
        ]);
    }

    /**
     * Test skill level tracking and updates
     */
    public function test_update_skill_level()
    {
        $skillCategory = 'grammar';
        $skillTopic = 'present_tense';
        $isCorrect = true;
        $responseTime = 5000; // 5 seconds

        // Test initial skill level creation
        $skillLevel = $this->adaptiveLearningService->updateSkillLevel(
            $this->user->id,
            $skillCategory,
            $skillTopic,
            $isCorrect,
            $responseTime
        );

        $this->assertInstanceOf(UserSkillLevel::class, $skillLevel);
        $this->assertEquals($this->user->id, $skillLevel->user_id);
        $this->assertEquals($skillCategory, $skillLevel->skill_category);
        $this->assertEquals($skillTopic, $skillLevel->skill_topic);
        $this->assertEquals(1, $skillLevel->correct_answers);
        $this->assertEquals(1, $skillLevel->total_attempts);
        $this->assertEquals(100, $skillLevel->accuracy_rate);
        $this->assertEquals(1, $skillLevel->consecutive_correct);

        // Test skill level update with incorrect answer
        $skillLevel = $this->adaptiveLearningService->updateSkillLevel(
            $this->user->id,
            $skillCategory,
            $skillTopic,
            false, // incorrect
            $responseTime
        );

        $this->assertEquals(1, $skillLevel->correct_answers); // Still 1
        $this->assertEquals(2, $skillLevel->total_attempts); // Now 2
        $this->assertEquals(50, $skillLevel->accuracy_rate); // 1/2 = 50%
        $this->assertEquals(0, $skillLevel->consecutive_correct); // Reset to 0
    }

    /**
     * Test mastery score calculation
     */
    public function test_mastery_score_calculation()
    {
        $skillLevel = UserSkillLevel::create([
            'user_id' => $this->user->id,
            'skill_category' => 'vocabulary',
            'skill_topic' => 'animals',
            'difficulty_level' => 'intermediate',
            'correct_answers' => 8,
            'total_attempts' => 10,
            'accuracy_rate' => 80,
            'consecutive_correct' => 3,
            'last_practiced_at' => now(),
            'performance_history' => json_encode([
                ['score' => 80, 'time' => 3000, 'date' => now()->subDays(2)->toISOString()],
                ['score' => 90, 'time' => 2500, 'date' => now()->subDay()->toISOString()],
                ['score' => 85, 'time' => 2800, 'date' => now()->toISOString()],
            ]),
        ]);

        $masteryScore = $this->adaptiveLearningService->calculateMasteryScore($skillLevel);

        // Mastery score should be between 0 and 100
        $this->assertGreaterThanOrEqual(0, $masteryScore);
        $this->assertLessThanOrEqual(100, $masteryScore);

        // With 80% accuracy, it should be a decent score
        $this->assertGreaterThan(60, $masteryScore);
    }

    /**
     * Test recommendation generation
     */
    public function test_generate_recommendations()
    {
        // Create skill levels with different mastery levels
        UserSkillLevel::create([
            'user_id' => $this->user->id,
            'skill_category' => 'grammar',
            'skill_topic' => 'past_tense',
            'mastery_score' => 45, // Low mastery - needs improvement
            'accuracy_rate' => 45,
            'total_attempts' => 10,
            'last_practiced_at' => now()->subDays(5),
        ]);

        UserSkillLevel::create([
            'user_id' => $this->user->id,
            'skill_category' => 'vocabulary',
            'skill_topic' => 'food',
            'mastery_score' => 85, // High mastery - needs review
            'accuracy_rate' => 90,
            'total_attempts' => 20,
            'last_practiced_at' => now()->subDays(10),
        ]);

        // Create recent quiz progress
        UserQuizProgress::create([
            'user_id' => $this->user->id,
            'section_id' => 'grammar',
            'score' => 70,
            'data' => json_encode(['questions' => 10, 'correct' => 7]),
            'created_at' => now()->subDays(2),
        ]);

        $recommendations = $this->adaptiveLearningService->generateRecommendations($this->user->id);

        $this->assertIsArray($recommendations);
        $this->assertNotEmpty($recommendations);

        // Check recommendation structure
        foreach ($recommendations as $recommendation) {
            $this->assertArrayHasKey('type', $recommendation);
            $this->assertArrayHasKey('title', $recommendation);
            $this->assertArrayHasKey('description', $recommendation);
            $this->assertArrayHasKey('priority', $recommendation);
            $this->assertArrayHasKey('confidence', $recommendation);
            $this->assertArrayHasKey('action_data', $recommendation);
        }

        // Should include skill improvement recommendation for low mastery skill
        $skillImprovementRecs = array_filter($recommendations, function($rec) {
            return $rec['type'] === 'skill_improvement';
        });
        $this->assertNotEmpty($skillImprovementRecs);

        // Should include review recommendation for high mastery skill not practiced recently
        $reviewRecs = array_filter($recommendations, function($rec) {
            return $rec['type'] === 'review_reminder';
        });
        $this->assertNotEmpty($reviewRecs);
    }

    /**
     * Test spaced repetition scheduling
     */
    public function test_get_spaced_repetition_items()
    {
        // Create skill levels with different last practice dates
        $skills = [
            [
                'skill_topic' => 'present_perfect',
                'mastery_score' => 60,
                'last_practiced_at' => now()->subDays(8), // Overdue
            ],
            [
                'skill_topic' => 'conditionals',
                'mastery_score' => 80,
                'last_practiced_at' => now()->subDays(3), // Due soon
            ],
            [
                'skill_topic' => 'passive_voice',
                'mastery_score' => 95,
                'last_practiced_at' => now()->subDay(), // Recently practiced
            ],
        ];

        foreach ($skills as $skillData) {
            UserSkillLevel::create(array_merge([
                'user_id' => $this->user->id,
                'skill_category' => 'grammar',
                'difficulty_level' => 'intermediate',
                'accuracy_rate' => 75,
                'total_attempts' => 10,
            ], $skillData));
        }

        $spacedRepetitionItems = $this->adaptiveLearningService->getSpacedRepetitionItems($this->user->id);

        $this->assertIsArray($spacedRepetitionItems);
        $this->assertNotEmpty($spacedRepetitionItems);

        // Check item structure
        foreach ($spacedRepetitionItems as $item) {
            $this->assertArrayHasKey('skill_topic', $item);
            $this->assertArrayHasKey('skill_category', $item);
            $this->assertArrayHasKey('mastery_score', $item);
            $this->assertArrayHasKey('days_since_practice', $item);
            $this->assertArrayHasKey('urgency_score', $item);
            $this->assertArrayHasKey('recommended_action', $item);
        }

        // Items should be sorted by urgency (highest first)
        $urgencyScores = array_column($spacedRepetitionItems, 'urgency_score');
        $sortedUrgencyScores = $urgencyScores;
        rsort($sortedUrgencyScores);
        $this->assertEquals($sortedUrgencyScores, $urgencyScores);

        // Most overdue item should have highest urgency
        $this->assertGreaterThan(80, $spacedRepetitionItems[0]['urgency_score']);
    }

    /**
     * Test skill level analysis
     */
    public function test_get_user_skill_levels()
    {
        // Create diverse skill levels
        $skillsData = [
            ['category' => 'grammar', 'topic' => 'present_tense', 'mastery' => 85],
            ['category' => 'grammar', 'topic' => 'past_tense', 'mastery' => 45],
            ['category' => 'vocabulary', 'topic' => 'food', 'mastery' => 90],
            ['category' => 'vocabulary', 'topic' => 'travel', 'mastery' => 60],
            ['category' => 'listening', 'topic' => 'conversations', 'mastery' => 75],
        ];

        foreach ($skillsData as $skill) {
            UserSkillLevel::create([
                'user_id' => $this->user->id,
                'skill_category' => $skill['category'],
                'skill_topic' => $skill['topic'],
                'mastery_score' => $skill['mastery'],
                'accuracy_rate' => $skill['mastery'] - rand(0, 10),
                'total_attempts' => rand(10, 50),
                'difficulty_level' => 'intermediate',
                'last_practiced_at' => now()->subDays(rand(1, 30)),
            ]);
        }

        $skillLevels = $this->adaptiveLearningService->getUserSkillLevels($this->user->id);

        $this->assertIsArray($skillLevels);
        $this->assertArrayHasKey('skills', $skillLevels);
        $this->assertArrayHasKey('categories', $skillLevels);
        $this->assertArrayHasKey('overall_progress', $skillLevels);

        // Check skills array
        $this->assertCount(5, $skillLevels['skills']);

        // Check categories analysis
        $categories = $skillLevels['categories'];
        $this->assertArrayHasKey('grammar', $categories);
        $this->assertArrayHasKey('vocabulary', $categories);
        $this->assertArrayHasKey('listening', $categories);

        // Grammar should have 2 skills
        $this->assertEquals(2, $categories['grammar']['skill_count']);
        
        // Vocabulary should have 2 skills
        $this->assertEquals(2, $categories['vocabulary']['skill_count']);

        // Overall progress should be calculated
        $this->assertArrayHasKey('total_skills', $skillLevels['overall_progress']);
        $this->assertArrayHasKey('average_mastery', $skillLevels['overall_progress']);
        $this->assertArrayHasKey('mastered_skills', $skillLevels['overall_progress']);
        $this->assertArrayHasKey('developing_skills', $skillLevels['overall_progress']);
        $this->assertArrayHasKey('struggling_skills', $skillLevels['overall_progress']);
    }

    /**
     * Test recommendation completion tracking
     */
    public function test_mark_recommendation_completed()
    {
        // Create a recommendation
        $recommendation = LearningRecommendation::create([
            'user_id' => $this->user->id,
            'recommendation_type' => 'skill_improvement',
            'title' => 'Practice Past Tense',
            'description' => 'Focus on past tense exercises',
            'action_type' => 'practice_quiz',
            'action_data' => json_encode(['skill' => 'past_tense', 'questions' => 10]),
            'priority' => 5,
            'confidence_score' => 85,
        ]);

        // Mark as completed
        $result = $this->adaptiveLearningService->markRecommendationCompleted(
            $recommendation->id,
            $this->user->id
        );

        $this->assertTrue($result);

        // Check if recommendation was updated
        $recommendation->refresh();
        $this->assertTrue($recommendation->is_completed);
        $this->assertNotNull($recommendation->completed_at);
    }

    /**
     * Test recommendation dismissal
     */
    public function test_dismiss_recommendation()
    {
        // Create a recommendation
        $recommendation = LearningRecommendation::create([
            'user_id' => $this->user->id,
            'recommendation_type' => 'new_content',
            'title' => 'Try Advanced Grammar',
            'description' => 'Explore advanced grammar topics',
            'action_type' => 'explore_content',
            'action_data' => json_encode(['content_type' => 'advanced_grammar']),
            'priority' => 3,
            'confidence_score' => 70,
        ]);

        // Dismiss recommendation
        $result = $this->adaptiveLearningService->dismissRecommendation(
            $recommendation->id,
            $this->user->id
        );

        $this->assertTrue($result);

        // Check if recommendation was updated
        $recommendation->refresh();
        $this->assertTrue($recommendation->is_dismissed);
    }

    /**
     * Test difficulty progression
     */
    public function test_difficulty_progression()
    {
        // Create a skill level with high mastery
        $skillLevel = UserSkillLevel::create([
            'user_id' => $this->user->id,
            'skill_category' => 'grammar',
            'skill_topic' => 'present_simple',
            'difficulty_level' => 'beginner',
            'mastery_score' => 90,
            'accuracy_rate' => 95,
            'consecutive_correct' => 8,
            'total_attempts' => 20,
            'last_practiced_at' => now(),
        ]);

        // Update with another correct answer - should trigger progression
        $updatedSkill = $this->adaptiveLearningService->updateSkillLevel(
            $this->user->id,
            'grammar',
            'present_simple',
            true, // correct
            3000
        );

        // With high mastery and consecutive correct answers, 
        // difficulty should progress from beginner
        $this->assertNotEquals('beginner', $updatedSkill->difficulty_level);
    }

    /**
     * Test learning insights generation
     */
    public function test_get_learning_insights()
    {
        // Create varied skill data for insights
        UserSkillLevel::create([
            'user_id' => $this->user->id,
            'skill_category' => 'grammar',
            'skill_topic' => 'conditionals',
            'mastery_score' => 95,
            'accuracy_rate' => 98,
            'total_attempts' => 50,
            'last_practiced_at' => now()->subDays(2),
        ]);

        UserSkillLevel::create([
            'user_id' => $this->user->id,
            'skill_category' => 'vocabulary',
            'skill_topic' => 'business',
            'mastery_score' => 35,
            'accuracy_rate' => 40,
            'total_attempts' => 20,
            'last_practiced_at' => now()->subDays(10),
        ]);

        $insights = $this->adaptiveLearningService->getLearningInsights($this->user->id);

        $this->assertIsArray($insights);
        $this->assertArrayHasKey('strengths', $insights);
        $this->assertArrayHasKey('weaknesses', $insights);
        $this->assertArrayHasKey('recommendations', $insights);
        $this->assertArrayHasKey('learning_velocity', $insights);
        $this->assertArrayHasKey('focus_areas', $insights);

        // Should identify conditionals as strength
        $strengths = $insights['strengths'];
        $this->assertNotEmpty($strengths);

        // Should identify business vocabulary as weakness
        $weaknesses = $insights['weaknesses'];
        $this->assertNotEmpty($weaknesses);
    }

    /**
     * Test edge cases and error handling
     */
    public function test_edge_cases()
    {
        // Test with invalid user ID
        $result = $this->adaptiveLearningService->updateSkillLevel(
            99999, 'grammar', 'test', true, 5000
        );
        $this->assertNull($result);

        // Test getting skill levels for user with no data
        $newUser = User::factory()->create();
        $skillLevels = $this->adaptiveLearningService->getUserSkillLevels($newUser->id);
        
        $this->assertIsArray($skillLevels);
        $this->assertEmpty($skillLevels['skills']);
        $this->assertEquals(0, $skillLevels['overall_progress']['total_skills']);

        // Test recommendations for user with no skill data
        $recommendations = $this->adaptiveLearningService->generateRecommendations($newUser->id);
        $this->assertIsArray($recommendations);
        // Should still generate some basic recommendations

        // Test spaced repetition for user with no skills
        $spacedItems = $this->adaptiveLearningService->getSpacedRepetitionItems($newUser->id);
        $this->assertIsArray($spacedItems);
        $this->assertEmpty($spacedItems);
    }
}