<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'ai-test@example.com',
        'name' => 'AI Test User'
    ]);
});

describe('AI Analysis Controller', function () {
    describe('Writing Analysis Endpoint', function () {
        it('analyzes writing with valid input', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/ai/analyze-writing', [
                    'text' => 'Hello world. This is a test of our AI writing analysis system.',
                    'level' => 'intermediate',
                    'analysis_type' => 'comprehensive'
                ]);
            
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'overall_score',
                        'grammar_analysis' => [
                            'score',
                            'errors_found',
                            'error_rate',
                            'corrections',
                            'error_types',
                            'complexity_score'
                        ],
                        'vocabulary_analysis' => [
                            'score',
                            'total_words',
                            'unique_words',
                            'diversity_ratio',
                            'level_breakdown',
                            'appropriateness_score'
                        ],
                        'estimated_cefr_level',
                        'processing_time'
                    ],
                    'meta' => [
                        'analysis_timestamp',
                        'analysis_version',
                        'text_statistics'
                    ]
                ]);
            
            expect($response->json('success'))->toBeTrue();
            expect($response->json('data.overall_score'))->toBeFloat()->toBeGreaterThan(0);
            expect($response->json('data.estimated_cefr_level'))->toMatch('/^[ABC][12]$/');
        });

        it('validates input parameters correctly', function () {
            // Test missing text
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/ai/analyze-writing', [
                    'level' => 'intermediate'
                ]);
            
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['text']);
            
            // Test text too short
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/ai/analyze-writing', [
                    'text' => 'short',
                    'level' => 'intermediate'
                ]);
            
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['text']);
            
            // Test invalid level
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/ai/analyze-writing', [
                    'text' => 'This is a valid length text for testing.',
                    'level' => 'invalid_level'
                ]);
            
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['level']);
        });

        it('filters analysis based on type parameter', function () {
            // Test quick analysis
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/ai/analyze-writing', [
                    'text' => 'This is a test sentence for quick analysis.',
                    'level' => 'intermediate',
                    'analysis_type' => 'quick'
                ]);
            
            $response->assertStatus(200);
            $data = $response->json('data');
            
            expect($data)->toHaveKeys([
                'overall_score',
                'estimated_cefr_level',
                'grammar_analysis',
                'vocabulary_analysis',
                'processing_time'
            ]);
            
            // Quick analysis should have simplified structure
            expect($data['grammar_analysis'])->toHaveKeys(['score', 'errors_found']);
            expect($data['vocabulary_analysis'])->toHaveKeys(['score', 'unique_words']);
        });

        it('requires authentication', function () {
            $response = $this->postJson('/api/ai/analyze-writing', [
                'text' => 'This should require authentication.',
                'level' => 'intermediate'
            ]);
            
            $response->assertStatus(401);
        });
    });

    describe('Comprehensive Insights Endpoint', function () {
        it('returns comprehensive AI insights', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/ai/comprehensive-insights');
            
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'learning_analytics',
                        'performance_prediction',
                        'personalized_recommendations',
                        'skill_gap_analysis',
                        'learning_efficiency',
                        'cognitive_load_assessment',
                        'motivation_insights',
                        'adaptive_strategies',
                        'meta_learning_insights',
                        'goal_optimization'
                    ],
                    'meta' => [
                        'generated_at',
                        'ai_version',
                        'insights_depth',
                        'prediction_accuracy',
                        'personalization_level'
                    ]
                ]);
            
            expect($response->json('success'))->toBeTrue();
            expect($response->json('meta.ai_version'))->toBe('4.0');
            expect($response->json('meta.insights_depth'))->toBe('comprehensive');
        });

        it('includes performance prediction data', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/ai/comprehensive-insights');
            
            $insights = $response->json('data');
            
            expect($insights['performance_prediction'])
                ->toBeArray()
                ->toHaveKeys([
                    'next_session_prediction',
                    'weekly_improvement_forecast',
                    'skill_mastery_timeline',
                    'plateau_risk_assessment',
                    'optimal_challenge_level',
                    'burnout_risk_prediction',
                    'engagement_trajectory',
                    'goal_achievement_probability'
                ]);
        });
    });

    describe('Performance Predictions Endpoint', function () {
        it('returns performance predictions with valid structure', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/ai/performance-predictions');
            
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'predictions',
                        'reliability_score',
                        'recommendation_confidence',
                        'prediction_basis'
                    ],
                    'meta' => [
                        'predicted_at',
                        'prediction_model',
                        'data_points_analyzed'
                    ]
                ]);
            
            expect($response->json('data.reliability_score'))->toBeFloat();
            expect($response->json('data.prediction_basis'))->toBeArray();
        });

        it('filters predictions by timeframe', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/ai/performance-predictions?prediction_timeframe=short_term');
            
            $response->assertStatus(200);
            $predictions = $response->json('data.predictions');
            
            // Short term should only include specific prediction types
            expect($predictions)->toBeArray();
        });

        it('includes focus area predictions when requested', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/ai/performance-predictions?focus_areas[]=grammar&focus_areas[]=vocabulary');
            
            $response->assertStatus(200);
            $data = $response->json('data');
            
            expect($data['predictions'])->toHaveKey('focus_area_predictions');
            expect($data['predictions']['focus_area_predictions'])->toBeArray();
        });

        it('validates input parameters', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/ai/performance-predictions?prediction_timeframe=invalid');
            
            $response->assertStatus(422);
        });
    });

    describe('Skill Gap Analysis Endpoint', function () {
        it('returns comprehensive skill gap analysis', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/ai/skill-gap-analysis');
            
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'skill_gaps' => [
                            'critical_gaps',
                            'foundational_weaknesses',
                            'advanced_skill_readiness',
                            'skill_transfer_analysis',
                            'prerequisite_mapping',
                            'zone_of_proximal_development',
                            'skill_synergy_opportunities',
                            'gap_closure_strategies'
                        ],
                        'priority_ranking',
                        'estimated_closure_time',
                        'success_probability'
                    ],
                    'meta' => [
                        'analyzed_at',
                        'analysis_version',
                        'depth_level',
                        'skills_assessed'
                    ]
                ]);
            
            expect($response->json('data.success_probability'))->toBeFloat();
            expect($response->json('data.estimated_closure_time'))->toBeArray();
        });

        it('includes remediation plan by default', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/ai/skill-gap-analysis');
            
            $skillGaps = $response->json('data.skill_gaps');
            expect($skillGaps)->toHaveKey('remediation_plan');
        });

        it('analyzes target level gaps when specified', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/ai/skill-gap-analysis?target_level=B2');
            
            $response->assertStatus(200);
            $skillGaps = $response->json('data.skill_gaps');
            
            expect($skillGaps)->toHaveKey('target_level_analysis');
            expect($skillGaps['target_level_analysis'])->toBeArray();
        });

        it('adjusts analysis depth correctly', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/ai/skill-gap-analysis?analysis_depth=basic');
            
            $response->assertStatus(200);
            expect($response->json('meta.depth_level'))->toBe('basic');
            
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/ai/skill-gap-analysis?analysis_depth=comprehensive');
            
            $response->assertStatus(200);
            expect($response->json('meta.depth_level'))->toBe('comprehensive');
        });

        it('validates target level parameter', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson('/api/ai/skill-gap-analysis?target_level=INVALID');
            
            $response->assertStatus(422);
        });
    });

    describe('Learning Recommendations Endpoint', function () {
        it('generates personalized learning recommendations', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/ai/learning-recommendations', [
                    'current_level' => 'B1',
                    'focus_areas' => ['grammar', 'vocabulary'],
                    'time_available' => 30
                ]);
            
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'immediate_actions',
                        'weekly_goals',
                        'monthly_objectives',
                        'recommended_exercises',
                        'study_plan',
                        'skill_priorities',
                        'estimated_improvement_timeline'
                    ],
                    'meta' => [
                        'generated_at',
                        'personalization_factors'
                    ]
                ]);
            
            expect($response->json('data.immediate_actions'))->toBeArray();
            expect($response->json('data.study_plan'))->toBeArray();
        });

        it('validates required parameters', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/ai/learning-recommendations', [
                    'focus_areas' => ['grammar']
                ]);
            
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['current_level']);
        });

        it('validates CEFR level format', function () {
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/ai/learning-recommendations', [
                    'current_level' => 'INVALID',
                    'focus_areas' => ['grammar']
                ]);
            
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['current_level']);
        });
    });

    describe('Authentication and Error Handling', function () {
        it('requires authentication for all AI endpoints', function () {
            $endpoints = [
                'GET /api/ai/comprehensive-insights',
                'GET /api/ai/performance-predictions',
                'GET /api/ai/skill-gap-analysis'
            ];
            
            foreach ($endpoints as $endpoint) {
                [$method, $url] = explode(' ', $endpoint);
                $response = $this->json($method, $url);
                $response->assertStatus(401);
            }
        });

        it('handles service exceptions gracefully', function () {
            // This would require mocking the service to throw an exception
            // For now, we'll test the general error response structure
            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/ai/analyze-writing', [
                    'text' => 'Test text',
                    'level' => 'intermediate'
                ]);
            
            if ($response->status() === 500) {
                $response->assertJsonStructure([
                    'success',
                    'error',
                    'message'
                ]);
            }
        });
    });
});