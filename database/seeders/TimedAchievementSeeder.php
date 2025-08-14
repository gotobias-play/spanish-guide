<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Achievement;

class TimedAchievementSeeder extends Seeder
{
    public function run(): void
    {
        $timedAchievements = [
            [
                'name' => 'Speed Demon',
                'description' => '⚡ Completa un quiz cronometrado con puntos bonus',
                'badge_icon' => '⚡',
                'badge_color' => '#FFFF00',
                'points_required' => 0,
                'condition_type' => 'speed_bonus_earned',
                'condition_value' => json_encode(['min_bonus' => 1]),
                'is_active' => true,
            ],
            [
                'name' => 'Lightning Fast',
                'description' => '🏃‍♂️ Obtén 10+ puntos bonus por velocidad en un quiz',
                'badge_icon' => '🏃‍♂️',
                'badge_color' => '#FF4500',
                'points_required' => 0,
                'condition_type' => 'speed_bonus_earned',
                'condition_value' => json_encode(['min_bonus' => 10]),
                'is_active' => true,
            ],
            [
                'name' => 'Time Master',
                'description' => '⏱️ Completa 5 quizzes cronometrados',
                'badge_icon' => '⏱️',
                'badge_color' => '#4169E1',
                'points_required' => 0,
                'condition_type' => 'timed_quizzes_completed',
                'condition_value' => json_encode(['count' => 5]),
                'is_active' => true,
            ],
            [
                'name' => 'Under Pressure',
                'description' => '💥 Obtén puntuación perfecta en un quiz cronometrado',
                'badge_icon' => '💥',
                'badge_color' => '#DC143C',
                'points_required' => 0,
                'condition_type' => 'timed_perfect_score',
                'condition_value' => json_encode(['score' => 100]),
                'is_active' => true,
            ]
        ];

        foreach ($timedAchievements as $achievement) {
            Achievement::create($achievement);
        }
    }
}