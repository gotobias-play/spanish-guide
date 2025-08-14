<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Course;
use App\Models\Achievement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantService
{
    /**
     * Create a new tenant with default setup
     */
    public function createTenant(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {
            // Create the tenant
            $tenant = Tenant::create([
                'name' => $data['name'],
                'subdomain' => $data['subdomain'],
                'domain' => $data['domain'] ?? null,
                'contact_email' => $data['contact_email'],
                'contact_phone' => $data['contact_phone'] ?? null,
                'description' => $data['description'] ?? null,
                'plan' => $data['plan'] ?? 'trial',
                'status' => 'trial',
                'trial_ends_at' => now()->addDays(30),
                'max_users' => $this->getMaxUsersForPlan($data['plan'] ?? 'trial'),
                'max_courses' => $this->getMaxCoursesForPlan($data['plan'] ?? 'trial'),
                'timezone' => $data['timezone'] ?? 'UTC',
                'locale' => $data['locale'] ?? 'es',
                'allowed_locales' => $data['allowed_locales'] ?? ['es', 'en'],
                'features' => $this->getFeaturesForPlan($data['plan'] ?? 'trial'),
                'settings' => $data['settings'] ?? [],
                'primary_color' => $data['primary_color'] ?? '#4A55A2',
                'secondary_color' => $data['secondary_color'] ?? '#7209B7',
            ]);

            // Create admin user for the tenant
            if (isset($data['admin_user'])) {
                $this->createTenantAdmin($tenant, $data['admin_user']);
            }

            // Set up default content
            $this->setupDefaultContent($tenant);

            return $tenant;
        });
    }

    /**
     * Create admin user for tenant
     */
    public function createTenantAdmin(Tenant $tenant, array $userData): User
    {
        return User::create([
            'tenant_id' => $tenant->id,
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Set up default content for new tenant
     */
    public function setupDefaultContent(Tenant $tenant): void
    {
        // Temporarily set the tenant context
        $originalTenantId = config('app.tenant_id');
        config(['app.tenant_id' => $tenant->id]);

        try {
            // Create default courses
            $this->createDefaultCourses($tenant);
            
            // Create default achievements
            $this->createDefaultAchievements($tenant);
            
        } finally {
            // Restore original tenant context
            config(['app.tenant_id' => $originalTenantId]);
        }
    }

    /**
     * Create default courses for tenant
     */
    private function createDefaultCourses(Tenant $tenant): void
    {
        $defaultCourses = [
            [
                'title' => 'Basic English Grammar',
                'description' => 'Learn the fundamentals of English grammar including verb "to be" and "to have".',
                'is_published' => true,
            ],
            [
                'title' => 'English for Daily Life',
                'description' => 'Practice English for everyday situations and conversations.',
                'is_published' => true,
            ],
            [
                'title' => 'Exploring My City',
                'description' => 'Learn prepositions and location vocabulary for describing places.',
                'is_published' => true,
            ],
        ];

        foreach ($defaultCourses as $courseData) {
            Course::create(array_merge($courseData, ['tenant_id' => $tenant->id]));
        }
    }

    /**
     * Create default achievements for tenant
     */
    private function createDefaultAchievements(Tenant $tenant): void
    {
        $defaultAchievements = [
            [
                'name' => 'First Steps',
                'description' => 'Complete your first quiz',
                'badge_icon' => '🌟',
                'badge_color' => '#FFD700',
                'points_required' => 0,
                'condition_type' => 'quiz_completed',
                'condition_value' => json_encode(['count' => 1]),
                'is_active' => true,
            ],
            [
                'name' => 'Quiz Master',
                'description' => 'Complete 5 quizzes',
                'badge_icon' => '🎓',
                'badge_color' => '#4A90E2',
                'points_required' => 0,
                'condition_type' => 'quiz_completed',
                'condition_value' => json_encode(['count' => 5]),
                'is_active' => true,
            ],
            [
                'name' => 'Perfect Score',
                'description' => 'Get 100% on any quiz',
                'badge_icon' => '💯',
                'badge_color' => '#50E3C2',
                'points_required' => 0,
                'condition_type' => 'perfect_score',
                'condition_value' => json_encode(['score' => 100]),
                'is_active' => true,
            ],
        ];

        foreach ($defaultAchievements as $achievementData) {
            Achievement::create(array_merge($achievementData, ['tenant_id' => $tenant->id]));
        }
    }

    /**
     * Get max users for plan
     */
    private function getMaxUsersForPlan(string $plan): int
    {
        return match ($plan) {
            'trial' => 10,
            'basic' => 50,
            'premium' => 200,
            'enterprise' => 1000,
            default => 10,
        };
    }

    /**
     * Get max courses for plan
     */
    private function getMaxCoursesForPlan(string $plan): int
    {
        return match ($plan) {
            'trial' => 3,
            'basic' => 10,
            'premium' => 50,
            'enterprise' => 999,
            default => 3,
        };
    }

    /**
     * Get features for plan
     */
    private function getFeaturesForPlan(string $plan): array
    {
        $baseFeatures = ['quizzes', 'progress_tracking', 'basic_analytics'];
        
        return match ($plan) {
            'trial' => $baseFeatures,
            'basic' => array_merge($baseFeatures, ['gamification', 'certificates']),
            'premium' => array_merge($baseFeatures, [
                'gamification', 'certificates', 'advanced_analytics', 
                'social_features', 'ai_features'
            ]),
            'enterprise' => array_merge($baseFeatures, [
                'gamification', 'certificates', 'advanced_analytics',
                'social_features', 'ai_features', 'custom_branding',
                'api_access', 'sso', 'custom_domains'
            ]),
            default => $baseFeatures,
        };
    }

    /**
     * Update tenant plan
     */
    public function updateTenantPlan(Tenant $tenant, string $newPlan): Tenant
    {
        $tenant->update([
            'plan' => $newPlan,
            'max_users' => $this->getMaxUsersForPlan($newPlan),
            'max_courses' => $this->getMaxCoursesForPlan($newPlan),
            'features' => $this->getFeaturesForPlan($newPlan),
            'status' => 'active',
        ]);

        return $tenant->fresh();
    }

    /**
     * Suspend tenant
     */
    public function suspendTenant(Tenant $tenant, string $reason = null): Tenant
    {
        $settings = $tenant->settings ?? [];
        $settings['suspension_reason'] = $reason;
        $settings['suspended_at'] = now()->toISOString();

        $tenant->update([
            'status' => 'suspended',
            'settings' => $settings,
        ]);

        return $tenant->fresh();
    }

    /**
     * Reactivate tenant
     */
    public function reactivateTenant(Tenant $tenant): Tenant
    {
        $settings = $tenant->settings ?? [];
        unset($settings['suspension_reason'], $settings['suspended_at']);
        $settings['reactivated_at'] = now()->toISOString();

        $tenant->update([
            'status' => 'active',
            'settings' => $settings,
        ]);

        return $tenant->fresh();
    }

    /**
     * Get tenant statistics
     */
    public function getTenantStats(Tenant $tenant): array
    {
        // Temporarily set tenant context
        $originalTenantId = config('app.tenant_id');
        config(['app.tenant_id' => $tenant->id]);

        try {
            return [
                'users' => [
                    'total' => $tenant->users()->count(),
                    'active' => $tenant->users()->where('email_verified_at', '!=', null)->count(),
                    'admins' => $tenant->users()->where('is_admin', true)->count(),
                ],
                'content' => [
                    'courses' => $tenant->courses()->count(),
                    'lessons' => $tenant->lessons()->count(),
                    'quizzes' => $tenant->quizzes()->count(),
                    'questions' => $tenant->questions()->count(),
                ],
                'activity' => [
                    'quiz_attempts' => DB::table('user_quiz_progress')
                        ->where('tenant_id', $tenant->id)
                        ->count(),
                    'certificates_issued' => $tenant->certificates()->count(),
                    'total_points_awarded' => DB::table('user_points')
                        ->where('tenant_id', $tenant->id)
                        ->sum('points'),
                ],
                'usage' => $tenant->getUsagePercentage(),
            ];
        } finally {
            config(['app.tenant_id' => $originalTenantId]);
        }
    }

    /**
     * Validate subdomain
     */
    public function validateSubdomain(string $subdomain): bool
    {
        // Check format (alphanumeric and hyphens, 3-63 characters)
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\-]{1,61}[a-zA-Z0-9]$/', $subdomain)) {
            return false;
        }

        // Check for reserved subdomains
        $reserved = ['www', 'api', 'admin', 'app', 'mail', 'ftp', 'blog', 'support'];
        if (in_array(strtolower($subdomain), $reserved)) {
            return false;
        }

        // Check if already exists
        return !Tenant::where('subdomain', $subdomain)->exists();
    }

    /**
     * Generate unique subdomain suggestion
     */
    public function generateSubdomainSuggestion(string $baseName): string
    {
        $subdomain = Str::slug($baseName);
        $counter = 1;

        while (!$this->validateSubdomain($subdomain)) {
            $subdomain = Str::slug($baseName) . '-' . $counter;
            $counter++;
            
            // Prevent infinite loop
            if ($counter > 999) {
                $subdomain = Str::slug($baseName) . '-' . Str::random(4);
                break;
            }
        }

        return $subdomain;
    }
}