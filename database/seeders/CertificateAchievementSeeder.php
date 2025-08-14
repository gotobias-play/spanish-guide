<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Achievement;

class CertificateAchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $certificateAchievements = [
            [
                'name' => 'Primer Certificado',
                'description' => 'Obtén tu primer certificado de finalización',
                'badge_icon' => '🏆',
                'badge_color' => 'gold',
                'points_required' => 0,
                'condition_type' => 'certificates_earned',
                'condition_value' => json_encode(['count' => 1]),
                'is_active' => true,
            ],
            [
                'name' => 'Coleccionista de Certificados',
                'description' => 'Obtén 3 certificados de finalización',
                'badge_icon' => '🏆',
                'badge_color' => 'purple',
                'points_required' => 0,
                'condition_type' => 'certificates_earned',
                'condition_value' => json_encode(['count' => 3]),
                'is_active' => true,
            ],
            [
                'name' => 'Maestro Certificado',
                'description' => 'Obtén certificados en todos los cursos disponibles',
                'badge_icon' => '👑',
                'badge_color' => 'gold',
                'points_required' => 0,
                'condition_type' => 'all_courses_certified',
                'condition_value' => json_encode(['required' => true]),
                'is_active' => true,
            ],
            [
                'name' => 'Excelencia Académica',
                'description' => 'Obtén un certificado con nivel "Excelente" (95%+)',
                'badge_icon' => '⭐',
                'badge_color' => 'gold',
                'points_required' => 0,
                'condition_type' => 'excellent_certificate',
                'condition_value' => json_encode(['min_score' => 95]),
                'is_active' => true,
            ],
            [
                'name' => 'Perfeccionista',
                'description' => 'Obtén 3 certificados con nivel "Excelente"',
                'badge_icon' => '💎',
                'badge_color' => 'diamond',
                'points_required' => 0,
                'condition_type' => 'excellent_certificates',
                'condition_value' => json_encode(['count' => 3, 'min_score' => 95]),
                'is_active' => true,
            ],
            [
                'name' => 'Completador Rápido',
                'description' => 'Obtén un certificado completando un curso en menos de 7 días',
                'badge_icon' => '⚡',
                'badge_color' => 'yellow',
                'points_required' => 0,
                'condition_type' => 'quick_course_completion',
                'condition_value' => json_encode(['max_days' => 7]),
                'is_active' => true,
            ],
        ];

        foreach ($certificateAchievements as $achievement) {
            Achievement::updateOrCreate(
                ['name' => $achievement['name']],
                $achievement
            );
        }
    }
}