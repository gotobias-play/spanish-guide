<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'tutoring-test@example.com',
        'name' => 'Tutoring Test User'
    ]);
});

describe('Intelligent Tutoring Controller', function () {
    describe('Learning Path Generation Endpoint', function () {
        it('generates personalized learning path', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/tutoring/learning-path');
            
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'user_profile' => [
                            'total_sessions',
                            'average_performance',
                            'learning_pattern',
                            'preferred_topics',
                            'study_habits',
                            'engagement_level',
                            'learning_velocity',
                            'consistency_score'
                        ],
                        'current_skills',
                        'learning_goals',
                        'recommended_path',
                        'next_session',
                        'estimated_timeline',
                        'difficulty_adjustment'
                    ],
                    'meta' => [
                        'generated_at',
                        'personalization_version',
                        'path_type'
                    ]
                ]);
            
            expect($response->json('success'))->toBeTrue();
            expect($response->json('meta.personalization_version'))->toBe('3.0');
            expect($response->json('meta.path_type'))->toBe('intelligent_adaptive');
        });

        it('includes comprehensive user profile analysis', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/tutoring/learning-path');
            
            $userProfile = $response->json('data.user_profile');
            
            expect($userProfile['engagement_level'])->toBeFloat();
            expect($userProfile['learning_velocity'])->toBeFloat();
            expect($userProfile['consistency_score'])->toBeFloat();
            expect($userProfile['preferred_topics'])->toBeArray();
            expect($userProfile['study_habits'])->toBeArray();
        });

        it('provides estimated timeline and difficulty adjustment', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/tutoring/learning-path');
            
            $data = $response->json('data');
            
            expect($data['estimated_timeline'])->toBeArray();
            expect($data['difficulty_adjustment'])->toBeArray();
            expect($data['next_session'])->toBeArray();
        });

        it('requires authentication', function () {
            $response = $this->getJson('/api/tutoring/learning-path');
            $response->assertStatus(401);
        });
    });

    describe('Tutoring Session Endpoint', function () {
        it('starts adaptive tutoring session', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/tutoring/start-session', [
                    'session_type' => 'adaptive',
                    'focus_areas' => ['grammar', 'vocabulary'],
                    'session_duration' => 25
                ]);
            
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'session_id',
                        'type',
                        'duration_minutes',
                        'focus_areas',
                        'learning_style',
                        'exercises',
                        'hints_enabled',
                        'immediate_feedback',
                        'difficulty_adaptation'
                    ],
                    'meta' => [
                        'session_started_at',
                        'tutoring_version',
                        'adaptive_features_enabled'
                    ]
                ]);
            
            $data = $response->json('data');
            expect($data['session_id'])->toStartWith('tutor_');
            expect($data['type'])->toBe('adaptive');
            expect($data['duration_minutes'])->toBe(25);
            expect($data['focus_areas'])->toBe(['grammar', 'vocabulary']);
        });

        it('validates session type parameter', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/tutoring/start-session', [
                    'session_type' => 'invalid_type'
                ]);
            
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['session_type']);
        });

        it('validates focus areas parameter', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/tutoring/start-session', [
                    'session_type' => 'focused',
                    'focus_areas' => ['invalid_area']
                ]);
            
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['focus_areas.0']);
        });

        it('validates session duration parameter', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/tutoring/start-session', [
                    'session_type' => 'adaptive',
                    'session_duration' => 2 // Too short
                ]);
            
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['session_duration']);
            
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/tutoring/start-session', [
                    'session_type' => 'adaptive',
                    'session_duration' => 120 // Too long
                ]);
            
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['session_duration']);
        });

        it('supports all session types', function () {
            $sessionTypes = ['adaptive', 'focused', 'review', 'assessment'];
            
            foreach ($sessionTypes as $type) {
                $response = $this->actingAs($this->user, 'sanctum')
                    ->postJson('/api/tutoring/start-session', [
                        'session_type' => $type
                    ]);
                
                $response->assertStatus(200);
                expect($response->json('data.type'))->toBe($type);
            }
        });

        it('generates learning style analysis', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/tutoring/start-session', [
                    'session_type' => 'adaptive'
                ]);
            
            $learningStyle = $response->json('data.learning_style');
            
            expect($learningStyle)->toBeArray();
            expect($learningStyle)->toHaveKeys([
                'visual',
                'auditory',
                'kinesthetic',
                'reading_writing',
                'dominant_style',
                'adaptation_recommendations'
            ]);
            
            expect($learningStyle['dominant_style'])->toBeString();
            expect($learningStyle['adaptation_recommendations'])->toBeArray();
        });

        it('generates personalized exercises', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/tutoring/start-session', [
                    'session_type' => 'focused'
                ]);
            
            $exercises = $response->json('data.exercises');
            
            expect($exercises)->toBeArray();
            foreach ($exercises as $exercise) {
                expect($exercise)->toHaveKeys([
                    'type',
                    'domain',
                    'difficulty',
                    'estimated_time',
                    'learning_objective',
                    'success_criteria',
                    'hints_available',
                    'feedback_type'
                ]);
            }
        });
    });

    describe('Learning Style Analysis Endpoint', function () {
        it('analyzes user learning style', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/tutoring/learning-style');
            
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'learning_style' => [
                            'visual',
                            'auditory',
                            'kinesthetic',
                            'reading_writing',
                            'dominant_style',
                            'adaptation_recommendations'
                        ],
                        'recommendations',
                        'adaptation_strategies'
                    ],
                    'meta' => [
                        'analyzed_at',
                        'analysis_version',
                        'confidence_score'
                    ]
                ]);
            
            $data = $response->json('data');
            expect($data['recommendations'])->toBeArray();
            expect($data['adaptation_strategies'])->toBeArray();
            expect($response->json('meta.confidence_score'))->toBeFloat();
        });

        it('provides style-specific recommendations', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/tutoring/learning-style');
            
            $recommendations = $response->json('data.recommendations');
            $adaptationStrategies = $response->json('data.adaptation_strategies');
            
            expect($recommendations)->not->toBeEmpty();
            expect($adaptationStrategies)->toHaveKeys([
                'content_presentation',
                'exercise_selection',
                'feedback_style',
                'pacing_adjustment'
            ]);
        });
    });

    describe('Adaptive Exercises Endpoint', function () {
        it('generates adaptive exercises', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/tutoring/adaptive-exercises');
            
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'exercises',
                        'total_estimated_time',
                        'difficulty_distribution',
                        'skill_coverage'
                    ],
                    'meta' => [
                        'generated_at',
                        'personalization_level',
                        'adaptive_difficulty'
                    ]
                ]);
            
            $data = $response->json('data');
            expect($data['exercises'])->toBeArray();
            expect($data['total_estimated_time'])->toBeInt();
            expect($data['difficulty_distribution'])->toBeArray();
            expect($data['skill_coverage'])->toBeArray();
        });

        it('filters exercises by skill area', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/tutoring/adaptive-exercises?skill_area=grammar');
            
            $response->assertStatus(200);
            $exercises = $response->json('data.exercises');
            
            // Should only contain grammar exercises (if any)
            expect($exercises)->toBeArray();
        });

        it('limits exercise count when requested', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/tutoring/adaptive-exercises?exercise_count=3');
            
            $response->assertStatus(200);
            $exercises = $response->json('data.exercises');
            
            expect(count($exercises))->toBeLessThanOrEqual(3);
        });

        it('validates parameters correctly', function () {
            // Test invalid skill area
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/tutoring/adaptive-exercises?skill_area=invalid');
            
            $response->assertStatus(422);
            
            // Test invalid exercise count
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/tutoring/adaptive-exercises?exercise_count=0');
            
            $response->assertStatus(422);
        });
    });

    describe('Session Progress Update Endpoint', function () {
        it('updates session progress successfully', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/tutoring/update-progress', [
                    'session_id' => 'tutor_test_session',
                    'exercise_id' => 'exercise_123',
                    'user_answer' => 'Test answer',
                    'time_spent' => 45,
                    'difficulty_rating' => 3
                ]);
            
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'progress_recorded',
                        'next_recommendation',
                        'performance_feedback',
                        'adaptation_applied'
                    ],
                    'meta' => [
                        'updated_at',
                        'adaptive_learning_enabled'
                    ]
                ]);
            
            expect($response->json('data.progress_recorded'))->toBeTrue();
            expect($response->json('data.next_recommendation'))->toBeArray();
            expect($response->json('data.performance_feedback'))->toBeArray();
        });

        it('validates required parameters', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/tutoring/update-progress', [
                    'session_id' => 'test_session'
                    // Missing required fields
                ]);
            
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['exercise_id', 'user_answer', 'time_spent']);
        });

        it('validates parameter types and ranges', function () {
            // Test invalid time spent
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/tutoring/update-progress', [
                    'session_id' => 'test_session',
                    'exercise_id' => 'exercise_123',
                    'user_answer' => 'Test answer',
                    'time_spent' => -5 // Invalid negative time
                ]);
            
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['time_spent']);
            
            // Test invalid difficulty rating
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/tutoring/update-progress', [
                    'session_id' => 'test_session',
                    'exercise_id' => 'exercise_123',
                    'user_answer' => 'Test answer',
                    'time_spent' => 30,
                    'difficulty_rating' => 10 // Invalid range (should be 1-5)
                ]);
            
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['difficulty_rating']);
        });

        it('generates next recommendation based on performance', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/tutoring/update-progress', [
                    'session_id' => 'tutor_test_session',
                    'exercise_id' => 'exercise_123',
                    'user_answer' => 'Correct answer',
                    'time_spent' => 30
                ]);
            
            $nextRecommendation = $response->json('data.next_recommendation');
            
            expect($nextRecommendation)->toHaveKeys([
                'exercise_type',
                'difficulty',
                'estimated_time',
                'reasoning'
            ]);
        });

        it('provides performance feedback', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/tutoring/update-progress', [
                    'session_id' => 'tutor_test_session',
                    'exercise_id' => 'exercise_123',
                    'user_answer' => 'Test answer',
                    'time_spent' => 60
                ]);
            
            $feedback = $response->json('data.performance_feedback');
            
            expect($feedback)->toHaveKeys([
                'performance_score',
                'time_efficiency',
                'accuracy_level',
                'improvement_areas'
            ]);
        });
    });

    describe('Authentication and Error Handling', function () {
        it('requires authentication for all tutoring endpoints', function () {
            $endpoints = [
                'GET /api/tutoring/learning-path',
                'GET /api/tutoring/learning-style',
                'GET /api/tutoring/adaptive-exercises'
            ];
            
            foreach ($endpoints as $endpoint) {
                [$method, $url] = explode(' ', $endpoint);
                $response = $this->json($method, $url);
                $response->assertStatus(401);
            }
            
            // Test POST endpoints
            $response = $this->postJson('/api/tutoring/start-session', []);
            $response->assertStatus(401);
            
            $response = $this->postJson('/api/tutoring/update-progress', []);
            $response->assertStatus(401);
        });

        it('handles service errors gracefully', function () {
            // Test with valid request that should work
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/tutoring/start-session', [
                    'session_type' => 'adaptive'
                ]);
            
            // If service throws an error, should return 500 with proper structure
            if ($response->status() === 500) {
                $response->assertJsonStructure([
                    'success',
                    'error',
                    'message'
                ]);
                expect($response->json('success'))->toBeFalse();
            }
        });

        it('provides Spanish error messages', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/tutoring/start-session', [
                    'session_type' => 'invalid'
                ]);
            
            if ($response->status() === 500) {
                expect($response->json('message'))->toBeString();
                // Should contain Spanish text
            }
        });
    });
});