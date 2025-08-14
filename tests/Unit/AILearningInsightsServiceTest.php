<?php

use App\Models\User;
use App\Services\AILearningInsightsService;
use App\Services\AIWritingAnalysisService;
use App\Services\IntelligentTutoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'insights-test@example.com',
        'name' => 'Insights Test User'
    ]);
    
    $this->writingService = new AIWritingAnalysisService();
    $this->tutoringService = new IntelligentTutoringService();
    $this->insightsService = new AILearningInsightsService(
        $this->writingService,
        $this->tutoringService
    );
    
    Cache::flush();
});

describe('AILearningInsightsService', function () {
    it('generates comprehensive insights with all required categories', function () {
        $insights = $this->insightsService->generateComprehensiveInsights($this->user);
        
        expect($insights)
            ->toBeArray()
            ->toHaveKeys([
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
            ]);
        
        expect(count($insights))->toBe(10);
    });

    it('provides detailed learning analytics', function () {
        $insights = $this->insightsService->generateComprehensiveInsights($this->user);
        $analytics = $insights['learning_analytics'];
        
        expect($analytics)
            ->toBeArray()
            ->toHaveKeys([
                'learning_curve',
                'performance_volatility',
                'knowledge_retention',
                'difficulty_adaptation',
                'topic_mastery_progression',
                'time_based_patterns',
                'error_pattern_analysis',
                'learning_acceleration'
            ]);
        
        // Test learning curve structure
        expect($analytics['learning_curve'])
            ->toBeArray()
            ->toHaveKeys(['slope', 'r_squared', 'acceleration']);
        
        expect($analytics['learning_curve']['slope'])->toBeFloat();
        expect($analytics['learning_curve']['r_squared'])->toBeFloat()->toBeLessThanOrEqual(1.0);
        
        // Test performance volatility
        expect($analytics['performance_volatility'])->toBeFloat()->toBeGreaterThanOrEqual(0);
        
        // Test knowledge retention
        expect($analytics['knowledge_retention'])
            ->toBeArray()
            ->toHaveKeys(['short_term', 'long_term', 'forgetting_curve']);
    });

    it('generates accurate performance predictions', function () {
        $insights = $this->insightsService->generateComprehensiveInsights($this->user);
        $predictions = $insights['performance_prediction'];
        
        expect($predictions)
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
        
        // Test next session prediction
        expect($predictions['next_session_prediction'])
            ->toBeArray()
            ->toHaveKeys(['predicted_score', 'confidence']);
        
        expect($predictions['next_session_prediction']['predicted_score'])
            ->toBeInt()
            ->toBeGreaterThanOrEqual(0)
            ->toBeLessThanOrEqual(100);
        
        // Test goal achievement probability
        expect($predictions['goal_achievement_probability'])
            ->toBeFloat()
            ->toBeGreaterThanOrEqual(0)
            ->toBeLessThanOrEqual(100);
    });

    it('analyzes skill gaps comprehensively', function () {
        $insights = $this->insightsService->generateComprehensiveInsights($this->user);
        $skillGaps = $insights['skill_gap_analysis'];
        
        expect($skillGaps)
            ->toBeArray()
            ->toHaveKeys([
                'critical_gaps',
                'foundational_weaknesses',
                'advanced_skill_readiness',
                'skill_transfer_analysis',
                'prerequisite_mapping',
                'zone_of_proximal_development',
                'skill_synergy_opportunities',
                'gap_closure_strategies'
            ]);
        
        expect($skillGaps['critical_gaps'])->toBeArray();
        expect($skillGaps['foundational_weaknesses'])->toBeArray();
        expect($skillGaps['gap_closure_strategies'])->toBeArray();
        
        // Test zone of proximal development
        expect($skillGaps['zone_of_proximal_development'])
            ->toBeArray()
            ->toHaveKeys(['current_level', 'zpd_level']);
    });

    it('evaluates learning efficiency metrics', function () {
        $insights = $this->insightsService->generateComprehensiveInsights($this->user);
        $efficiency = $insights['learning_efficiency'];
        
        expect($efficiency)
            ->toBeArray()
            ->toHaveKeys([
                'time_to_mastery_ratio',
                'effort_to_outcome_efficiency',
                'cognitive_load_optimization',
                'attention_focus_analysis',
                'memory_consolidation_efficiency',
                'practice_distribution_effectiveness',
                'feedback_utilization_rate',
                'self_regulation_effectiveness'
            ]);
        
        expect($efficiency['time_to_mastery_ratio'])->toBeFloat()->toBeGreaterThan(0);
        expect($efficiency['effort_to_outcome_efficiency'])->toBeFloat()->toBeGreaterThan(0);
        
        // Test feedback utilization
        expect($efficiency['feedback_utilization_rate'])
            ->toBeArray()
            ->toHaveKeys(['utilization_rate', 'integration_success']);
    });

    it('assesses cognitive load accurately', function () {
        $insights = $this->insightsService->generateComprehensiveInsights($this->user);
        $cognitiveLoad = $insights['cognitive_load_assessment'];
        
        expect($cognitiveLoad)
            ->toBeArray()
            ->toHaveKeys([
                'current_cognitive_load',
                'optimal_load_recommendation',
                'intrinsic_load_analysis',
                'extraneous_load_identification',
                'germane_load_optimization',
                'working_memory_utilization',
                'cognitive_flexibility_assessment',
                'load_balancing_strategies'
            ]);
        
        expect($cognitiveLoad['current_cognitive_load'])
            ->toBeFloat()
            ->toBeGreaterThanOrEqual(0)
            ->toBeLessThanOrEqual(100);
        
        expect($cognitiveLoad['optimal_load_recommendation'])
            ->toBeFloat()
            ->toBeGreaterThanOrEqual(0)
            ->toBeLessThanOrEqual(100);
        
        expect($cognitiveLoad['load_balancing_strategies'])->toBeArray();
    });

    it('analyzes motivation patterns effectively', function () {
        $insights = $this->insightsService->generateComprehensiveInsights($this->user);
        $motivation = $insights['motivation_insights'];
        
        expect($motivation)
            ->toBeArray()
            ->toHaveKeys([
                'intrinsic_motivation_level',
                'extrinsic_motivation_factors',
                'motivation_sustainability',
                'engagement_triggers',
                'flow_state_analysis',
                'goal_orientation_assessment',
                'self_efficacy_evaluation',
                'motivation_enhancement_strategies'
            ]);
        
        expect($motivation['intrinsic_motivation_level'])
            ->toBeFloat()
            ->toBeGreaterThanOrEqual(0)
            ->toBeLessThanOrEqual(100);
        
        expect($motivation['self_efficacy_evaluation'])
            ->toBeFloat()
            ->toBeGreaterThanOrEqual(0)
            ->toBeLessThanOrEqual(100);
        
        expect($motivation['extrinsic_motivation_factors'])->toBeArray();
        expect($motivation['motivation_enhancement_strategies'])->toBeArray();
    });

    it('recommends adaptive learning strategies', function () {
        $insights = $this->insightsService->generateComprehensiveInsights($this->user);
        $strategies = $insights['adaptive_strategies'];
        
        expect($strategies)
            ->toBeArray()
            ->toHaveKeys([
                'personalization_level',
                'adaptive_pacing',
                'content_adaptation',
                'assessment_adaptation',
                'feedback_adaptation',
                'interface_adaptation',
                'social_adaptation',
                'contextual_adaptation'
            ]);
        
        expect($strategies['personalization_level'])
            ->toBeFloat()
            ->toBeGreaterThanOrEqual(0)
            ->toBeLessThanOrEqual(100);
        
        expect($strategies['adaptive_pacing'])->toBeArray();
        expect($strategies['content_adaptation'])->toBeArray();
        expect($strategies['feedback_adaptation'])->toBeArray();
    });

    it('generates meta-learning insights', function () {
        $insights = $this->insightsService->generateComprehensiveInsights($this->user);
        $metaLearning = $insights['meta_learning_insights'];
        
        expect($metaLearning)
            ->toBeArray()
            ->toHaveKeys([
                'learning_strategy_effectiveness',
                'self_monitoring_skills',
                'metacognitive_awareness',
                'learning_transfer_ability',
                'strategy_selection_optimization',
                'self_regulation_development',
                'reflection_practice_analysis',
                'meta_strategy_recommendations'
            ]);
        
        expect($metaLearning['self_monitoring_skills'])
            ->toBeFloat()
            ->toBeGreaterThanOrEqual(0)
            ->toBeLessThanOrEqual(100);
        
        expect($metaLearning['learning_transfer_ability'])
            ->toBeFloat()
            ->toBeGreaterThanOrEqual(0)
            ->toBeLessThanOrEqual(100);
    });

    it('optimizes learning goals effectively', function () {
        $insights = $this->insightsService->generateComprehensiveInsights($this->user);
        $goalOptimization = $insights['goal_optimization'];
        
        expect($goalOptimization)
            ->toBeArray()
            ->toHaveKeys([
                'goal_specificity_analysis',
                'goal_achievability_assessment',
                'goal_relevance_evaluation',
                'goal_timing_optimization',
                'subgoal_decomposition',
                'goal_prioritization',
                'progress_milestone_design',
                'goal_adjustment_recommendations'
            ]);
        
        expect($goalOptimization['goal_achievability_assessment'])
            ->toBeFloat()
            ->toBeGreaterThanOrEqual(0)
            ->toBeLessThanOrEqual(100);
        
        expect($goalOptimization['subgoal_decomposition'])->toBeArray();
        expect($goalOptimization['goal_prioritization'])->toBeArray();
        expect($goalOptimization['progress_milestone_design'])->toBeArray();
    });

    it('implements intelligent caching for performance optimization', function () {
        // First call should generate insights
        $insights1 = $this->insightsService->generateComprehensiveInsights($this->user);
        
        // Second call should use cache
        $insights2 = $this->insightsService->generateComprehensiveInsights($this->user);
        
        // Results should be identical due to caching
        expect($insights1)->toBe($insights2);
        
        // Verify cache key exists
        $cacheKey = "ai_insights_user_{$this->user->id}";
        expect(Cache::has($cacheKey))->toBeTrue();
    });

    it('handles edge cases gracefully', function () {
        // Test with user with no data
        $newUser = User::factory()->create();
        
        $insights = $this->insightsService->generateComprehensiveInsights($newUser);
        
        expect($insights)->toBeArray();
        expect(count($insights))->toBe(10);
        
        // Should still return valid structure even with minimal data
        expect($insights['performance_prediction']['goal_achievement_probability'])
            ->toBeFloat()
            ->toBeGreaterThanOrEqual(0);
    });

    it('maintains consistent data types across all insights', function () {
        $insights = $this->insightsService->generateComprehensiveInsights($this->user);
        
        // Verify all top-level categories are arrays
        foreach ($insights as $category => $data) {
            expect($data)->toBeArray("Category {$category} should be an array");
        }
        
        // Verify numerical metrics are within expected ranges
        $motivation = $insights['motivation_insights'];
        expect($motivation['intrinsic_motivation_level'])->toBeBetween(0, 100);
        expect($motivation['self_efficacy_evaluation'])->toBeBetween(0, 100);
        
        $efficiency = $insights['learning_efficiency'];
        expect($efficiency['time_to_mastery_ratio'])->toBeGreaterThan(0);
        expect($efficiency['effort_to_outcome_efficiency'])->toBeGreaterThan(0);
    });
});