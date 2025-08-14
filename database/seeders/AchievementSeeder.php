<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Achievement;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            [
                'name' => 'First Steps',
                'description' => '¡Completa tu primer quiz!',
                'badge_icon' => '🌟',
                'badge_color' => '#FFD700',
                'points_required' => 0,
                'condition_type' => 'quizzes_completed',
                'condition_value' => json_encode(['count' => 1]),
            ],
            [
                'name' => 'Quiz Master',
                'description' => 'Completa 5 quizzes',
                'badge_icon' => '🎓',
                'badge_color' => '#4A90E2',
                'points_required' => 0,
                'condition_type' => 'quizzes_completed',
                'condition_value' => json_encode(['count' => 5]),
            ],
            [
                'name' => 'Perfect Score',
                'description' => 'Obtén 100% en un quiz',
                'badge_icon' => '💯',
                'badge_color' => '#FF6B6B',
                'points_required' => 0,
                'condition_type' => 'perfect_score',
                'condition_value' => json_encode(['score' => 100]),
            ],
            [
                'name' => 'Streak Starter',
                'description' => 'Mantén una racha de 3 días',
                'badge_icon' => '🔥',
                'badge_color' => '#FF8C00',
                'points_required' => 0,
                'condition_type' => 'streak',
                'condition_value' => json_encode(['days' => 3]),
            ],
            [
                'name' => 'Dedicated Learner',
                'description' => 'Mantén una racha de 7 días',
                'badge_icon' => '💪',
                'badge_color' => '#32CD32',
                'points_required' => 0,
                'condition_type' => 'streak',
                'condition_value' => json_encode(['days' => 7]),
            ],
            [
                'name' => 'Point Collector',
                'description' => 'Acumula 100 puntos',
                'badge_icon' => '💎',
                'badge_color' => '#9370DB',
                'points_required' => 0,
                'condition_type' => 'points',
                'condition_value' => json_encode(['total' => 100]),
            ],
            [
                'name' => 'Grammar Expert',
                'description' => 'Completa todos los quizzes de gramática',
                'badge_icon' => '📚',
                'badge_color' => '#20B2AA',
                'points_required' => 0,
                'condition_type' => 'course_completion',
                'condition_value' => json_encode(['course_id' => 1]),
            ],
            [
                'name' => 'Daily Life Pro',
                'description' => 'Completa todos los quizzes de vida diaria',
                'badge_icon' => '🌅',
                'badge_color' => '#FFA500',
                'points_required' => 0,
                'condition_type' => 'course_completion',
                'condition_value' => json_encode(['course_id' => 2]),
            ],
        ];

        foreach ($achievements as $achievement) {
            Achievement::create($achievement);
        }
    }
}