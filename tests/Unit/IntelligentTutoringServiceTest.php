<?php

use App\Models\User;
use App\Services\IntelligentTutoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'name' => 'Test User'
    ]);
    
    $this->tutoringService = new IntelligentTutoringService();
});

describe('IntelligentTutoringService', function () {
    it('can generate a personalized learning path', function () {
        $learningPath = $this->tutoringService->generateLearningPath($this->user);
        
        expect($learningPath)
            ->toBeArray()
            ->toHaveKeys([
                'user_profile',
                'current_skills', 
                'learning_goals',
                'recommended_path',
                'next_session',
                'estimated_timeline',
                'difficulty_adjustment'
            ]);
        
        expect($learningPath['user_profile'])
            ->toBeArray()
            ->toHaveKeys([
                'total_sessions',
                'average_performance',
                'learning_pattern',
                'preferred_topics',
                'study_habits',
                'engagement_level',
                'learning_velocity',
                'consistency_score'
            ]);
        
        expect($learningPath['user_profile']['engagement_level'])
            ->toBeFloat()
            ->toBeGreaterThan(0)
            ->toBeLessThanOrEqual(100);
        
        expect($learningPath['estimated_timeline'])
            ->toBeArray()
            ->toHaveKey('estimated_weeks');
    });

    it('can conduct different types of tutoring sessions', function () {
        $sessionTypes = ['adaptive', 'focused', 'review', 'assessment'];
        
        foreach ($sessionTypes as $type) {
            $session = $this->tutoringService->conductTutoringSession($this->user, $type);
            
            expect($session)
                ->toBeArray()
                ->toHaveKeys([
                    'session_id',
                    'type',
                    'duration_minutes',
                    'focus_areas',
                    'learning_style',
                    'exercises',
                    'hints_enabled',
                    'immediate_feedback',
                    'difficulty_adaptation'
                ]);
            
            expect($session['type'])->toBe($type);
            expect($session['session_id'])->toStartWith('tutor_');
            expect($session['duration_minutes'])->toBeInt()->toBeGreaterThan(0);
            expect($session['focus_areas'])->toBeArray();
            expect($session['exercises'])->toBeArray();
        }
    });

    it('generates learning style analysis with correct structure', function () {
        $session = $this->tutoringService->conductTutoringSession($this->user, 'adaptive');
        $learningStyle = $session['learning_style'];
        
        expect($learningStyle)
            ->toBeArray()
            ->toHaveKeys([
                'visual',
                'auditory', 
                'kinesthetic',
                'reading_writing',
                'dominant_style',
                'adaptation_recommendations'
            ]);
        
        expect($learningStyle['visual'])->toBeFloat()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);
        expect($learningStyle['auditory'])->toBeFloat()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);
        expect($learningStyle['kinesthetic'])->toBeFloat()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);
        expect($learningStyle['reading_writing'])->toBeFloat()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);
        
        expect($learningStyle['dominant_style'])->toBeString();
        expect($learningStyle['adaptation_recommendations'])->toBeArray();
    });

    it('generates personalized exercises with proper structure', function () {
        $session = $this->tutoringService->conductTutoringSession($this->user, 'focused');
        $exercises = $session['exercises'];
        
        expect($exercises)->toBeArray()->not->toBeEmpty();
        
        foreach ($exercises as $exercise) {
            expect($exercise)
                ->toBeArray()
                ->toHaveKeys([
                    'type',
                    'domain',
                    'difficulty',
                    'estimated_time',
                    'learning_objective',
                    'success_criteria',
                    'hints_available',
                    'feedback_type'
                ]);
            
            expect($exercise['estimated_time'])->toBeInt()->toBeGreaterThan(0);
            expect($exercise['success_criteria'])->toBeArray();
            expect($exercise['hints_available'])->toBeArray();
        }
    });

    it('provides consistent session configuration', function () {
        $session1 = $this->tutoringService->conductTutoringSession($this->user, 'adaptive');
        $session2 = $this->tutoringService->conductTutoringSession($this->user, 'adaptive');
        
        // Sessions should have similar structure but different IDs
        expect($session1['session_id'])->not->toBe($session2['session_id']);
        expect($session1['type'])->toBe($session2['type']);
        expect($session1['duration_minutes'])->toBe($session2['duration_minutes']);
        expect($session1['learning_style']['dominant_style'])
            ->toBe($session2['learning_style']['dominant_style']);
    });

    it('recommends appropriate session duration', function () {
        $session = $this->tutoringService->conductTutoringSession($this->user, 'adaptive');
        
        expect($session['duration_minutes'])
            ->toBeInt()
            ->toBeGreaterThanOrEqual(5)
            ->toBeLessThanOrEqual(60);
    });

    it('enables appropriate learning features', function () {
        $session = $this->tutoringService->conductTutoringSession($this->user, 'adaptive');
        
        expect($session['hints_enabled'])->toBeBool();
        expect($session['difficulty_adaptation'])->toBeBool();
        expect($session['immediate_feedback'])->toBeArray()->toHaveKey('enabled');
    });

    it('generates valid next session recommendations', function () {
        $learningPath = $this->tutoringService->generateLearningPath($this->user);
        $nextSession = $learningPath['next_session'];
        
        expect($nextSession)
            ->toBeArray()
            ->toHaveKeys([
                'recommended_time',
                'focus_areas',
                'structure',
                'duration_minutes',
                'difficulty_level',
                'preparation_activities',
                'follow_up_activities'
            ]);
        
        expect($nextSession['focus_areas'])->toBeArray();
        expect($nextSession['structure'])->toBeArray();
        expect($nextSession['duration_minutes'])->toBeInt()->toBeGreaterThan(0);
        expect($nextSession['preparation_activities'])->toBeArray();
        expect($nextSession['follow_up_activities'])->toBeArray();
    });
});