<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\TenantService;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Course;
use App\Models\Achievement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class TenantServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TenantService $tenantService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenantService = new TenantService();
    }

    /**
     * Test tenant creation
     */
    public function test_create_tenant()
    {
        $tenantData = [
            'name' => 'Test Language Academy',
            'subdomain' => 'test-academy',
            'admin_name' => 'John Doe',
            'admin_email' => 'admin@test-academy.com',
            'plan' => 'basic',
        ];

        $tenant = $this->tenantService->createTenant($tenantData);

        $this->assertInstanceOf(Tenant::class, $tenant);
        $this->assertEquals('Test Language Academy', $tenant->name);
        $this->assertEquals('test-academy', $tenant->subdomain);
        $this->assertEquals('basic', $tenant->plan);
        $this->assertEquals('active', $tenant->status);
        $this->assertNotNull($tenant->trial_ends_at);

        // Check if admin user was created
        $adminUser = User::where('email', 'admin@test-academy.com')
                        ->where('tenant_id', $tenant->id)
                        ->first();
        
        $this->assertNotNull($adminUser);
        $this->assertTrue($adminUser->is_admin);
        $this->assertEquals('John Doe', $adminUser->name);
    }

    /**
     * Test tenant setup with default content
     */
    public function test_setup_tenant_with_default_content()
    {
        $tenant = Tenant::create([
            'name' => 'Setup Test Academy',
            'subdomain' => 'setup-test',
            'domain' => null,
            'plan' => 'premium',
            'status' => 'active',
            'trial_ends_at' => now()->addDays(30),
            'settings' => json_encode([]),
            'features' => json_encode(['gamification', 'analytics', 'ai_features']),
            'limits' => json_encode(['users' => 200, 'courses' => 50]),
            'usage' => json_encode(['users' => 0, 'courses' => 0]),
            'branding' => json_encode([]),
            'billing_info' => json_encode([]),
            'locale' => 'es',
            'timezone' => 'Europe/Madrid',
        ]);

        $result = $this->tenantService->setupDefaultContent($tenant->id);

        $this->assertTrue($result);

        // Check if default courses were created
        $courses = Course::where('tenant_id', $tenant->id)->get();
        $this->assertGreaterThan(0, $courses->count());

        // Check if default achievements were created
        $achievements = Achievement::where('tenant_id', $tenant->id)->get();
        $this->assertGreaterThan(0, $achievements->count());
    }

    /**
     * Test plan upgrade/downgrade
     */
    public function test_change_tenant_plan()
    {
        $tenant = Tenant::create([
            'name' => 'Plan Change Test',
            'subdomain' => 'plan-test',
            'plan' => 'trial',
            'status' => 'active',
            'trial_ends_at' => now()->addDays(30),
            'features' => json_encode(['basic_features']),
            'limits' => json_encode(['users' => 10, 'courses' => 3]),
            'usage' => json_encode(['users' => 5, 'courses' => 2]),
            'branding' => json_encode([]),
            'billing_info' => json_encode([]),
            'locale' => 'en',
            'timezone' => 'UTC',
        ]);

        // Upgrade to premium
        $result = $this->tenantService->changePlan($tenant->id, 'premium');

        $this->assertTrue($result);

        $tenant->refresh();
        $this->assertEquals('premium', $tenant->plan);
        
        $features = json_decode($tenant->features, true);
        $this->assertContains('gamification', $features);
        $this->assertContains('analytics', $features);
        $this->assertContains('ai_features', $features);

        $limits = json_decode($tenant->limits, true);
        $this->assertEquals(200, $limits['users']);
        $this->assertEquals(50, $limits['courses']);
    }

    /**
     * Test feature checking
     */
    public function test_has_feature()
    {
        $tenant = Tenant::create([
            'name' => 'Feature Test',
            'subdomain' => 'feature-test',
            'plan' => 'premium',
            'status' => 'active',
            'features' => json_encode(['gamification', 'analytics', 'ai_features']),
            'limits' => json_encode(['users' => 200, 'courses' => 50]),
            'usage' => json_encode(['users' => 0, 'courses' => 0]),
            'branding' => json_encode([]),
            'billing_info' => json_encode([]),
            'locale' => 'en',
            'timezone' => 'UTC',
        ]);

        $this->assertTrue($this->tenantService->hasFeature($tenant->id, 'gamification'));
        $this->assertTrue($this->tenantService->hasFeature($tenant->id, 'analytics'));
        $this->assertTrue($this->tenantService->hasFeature($tenant->id, 'ai_features'));
        $this->assertFalse($this->tenantService->hasFeature($tenant->id, 'custom_branding'));
        $this->assertFalse($this->tenantService->hasFeature($tenant->id, 'api_access'));
    }

    /**
     * Test usage limit checking
     */
    public function test_check_usage_limits()
    {
        $tenant = Tenant::create([
            'name' => 'Usage Test',
            'subdomain' => 'usage-test',
            'plan' => 'basic',
            'status' => 'active',
            'features' => json_encode(['gamification']),
            'limits' => json_encode(['users' => 50, 'courses' => 10]),
            'usage' => json_encode(['users' => 45, 'courses' => 8]),
            'branding' => json_encode([]),
            'billing_info' => json_encode([]),
            'locale' => 'en',
            'timezone' => 'UTC',
        ]);

        // Test within limits
        $this->assertTrue($this->tenantService->canAddUsers($tenant->id, 3));
        $this->assertTrue($this->tenantService->canAddCourses($tenant->id, 2));

        // Test exceeding limits
        $this->assertFalse($this->tenantService->canAddUsers($tenant->id, 10));
        $this->assertFalse($this->tenantService->canAddCourses($tenant->id, 5));

        // Test exact limit
        $this->assertTrue($this->tenantService->canAddUsers($tenant->id, 5));
        $this->assertTrue($this->tenantService->canAddCourses($tenant->id, 2));
    }

    /**
     * Test usage tracking and updates
     */
    public function test_update_usage()
    {
        $tenant = Tenant::create([
            'name' => 'Usage Update Test',
            'subdomain' => 'usage-update',
            'plan' => 'premium',
            'status' => 'active',
            'features' => json_encode(['gamification', 'analytics']),
            'limits' => json_encode(['users' => 200, 'courses' => 50]),
            'usage' => json_encode(['users' => 10, 'courses' => 5]),
            'branding' => json_encode([]),
            'billing_info' => json_encode([]),
            'locale' => 'en',
            'timezone' => 'UTC',
        ]);

        // Create some users and courses for this tenant
        User::factory()->count(5)->create(['tenant_id' => $tenant->id]);
        Course::factory()->count(3)->create(['tenant_id' => $tenant->id]);

        // Update usage
        $this->tenantService->updateUsage($tenant->id);

        $tenant->refresh();
        $usage = json_decode($tenant->usage, true);

        // Should reflect actual counts
        $this->assertEquals(5, $usage['users']);
        $this->assertEquals(3, $usage['courses']);
    }

    /**
     * Test tenant statistics
     */
    public function test_get_tenant_statistics()
    {
        $tenant = Tenant::create([
            'name' => 'Stats Test',
            'subdomain' => 'stats-test',
            'plan' => 'enterprise',
            'status' => 'active',
            'features' => json_encode(['gamification', 'analytics', 'ai_features', 'custom_branding']),
            'limits' => json_encode(['users' => 1000, 'courses' => 100]),
            'usage' => json_encode(['users' => 150, 'courses' => 25]),
            'branding' => json_encode([]),
            'billing_info' => json_encode([]),
            'locale' => 'en',
            'timezone' => 'UTC',
            'created_at' => now()->subDays(15),
        ]);

        // Create some users for statistics
        User::factory()->count(10)->create(['tenant_id' => $tenant->id]);
        Course::factory()->count(5)->create(['tenant_id' => $tenant->id]);

        $stats = $this->tenantService->getTenantStatistics($tenant->id);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('tenant_info', $stats);
        $this->assertArrayHasKey('usage_stats', $stats);
        $this->assertArrayHasKey('plan_info', $stats);
        $this->assertArrayHasKey('activity_summary', $stats);

        // Check tenant info
        $this->assertEquals('Stats Test', $stats['tenant_info']['name']);
        $this->assertEquals('enterprise', $stats['tenant_info']['plan']);
        $this->assertEquals('active', $stats['tenant_info']['status']);

        // Check usage stats
        $this->assertArrayHasKey('users', $stats['usage_stats']);
        $this->assertArrayHasKey('courses', $stats['usage_stats']);
        $this->assertArrayHasKey('storage', $stats['usage_stats']);

        // Check plan info
        $this->assertArrayHasKey('features', $stats['plan_info']);
        $this->assertArrayHasKey('limits', $stats['plan_info']);
    }

    /**
     * Test subdomain validation
     */
    public function test_validate_subdomain()
    {
        // Create existing tenant
        Tenant::create([
            'name' => 'Existing Tenant',
            'subdomain' => 'existing-tenant',
            'plan' => 'basic',
            'status' => 'active',
            'features' => json_encode([]),
            'limits' => json_encode(['users' => 50, 'courses' => 10]),
            'usage' => json_encode(['users' => 0, 'courses' => 0]),
            'branding' => json_encode([]),
            'billing_info' => json_encode([]),
            'locale' => 'en',
            'timezone' => 'UTC',
        ]);

        // Test valid subdomain
        $this->assertTrue($this->tenantService->isSubdomainAvailable('new-tenant'));
        $this->assertTrue($this->tenantService->isSubdomainAvailable('valid-subdomain'));

        // Test existing subdomain
        $this->assertFalse($this->tenantService->isSubdomainAvailable('existing-tenant'));

        // Test invalid subdomains
        $this->assertFalse($this->tenantService->isSubdomainAvailable(''));
        $this->assertFalse($this->tenantService->isSubdomainAvailable('a')); // Too short
        $this->assertFalse($this->tenantService->isSubdomainAvailable('invalid_subdomain')); // Underscore
        $this->assertFalse($this->tenantService->isSubdomainAvailable('UPPERCASE')); // Uppercase
        $this->assertFalse($this->tenantService->isSubdomainAvailable('123')); // Numbers only

        // Test reserved subdomains
        $reservedSubdomains = ['www', 'admin', 'api', 'app', 'mail', 'ftp', 'test'];
        foreach ($reservedSubdomains as $reserved) {
            $this->assertFalse($this->tenantService->isSubdomainAvailable($reserved));
        }
    }

    /**
     * Test tenant suspension and reactivation
     */
    public function test_suspend_and_reactivate_tenant()
    {
        $tenant = Tenant::create([
            'name' => 'Suspension Test',
            'subdomain' => 'suspension-test',
            'plan' => 'basic',
            'status' => 'active',
            'features' => json_encode(['gamification']),
            'limits' => json_encode(['users' => 50, 'courses' => 10]),
            'usage' => json_encode(['users' => 20, 'courses' => 5]),
            'branding' => json_encode([]),
            'billing_info' => json_encode([]),
            'locale' => 'en',
            'timezone' => 'UTC',
        ]);

        // Test suspension
        $result = $this->tenantService->suspendTenant($tenant->id, 'Non-payment');
        $this->assertTrue($result);

        $tenant->refresh();
        $this->assertEquals('suspended', $tenant->status);

        // Test reactivation
        $result = $this->tenantService->reactivateTenant($tenant->id);
        $this->assertTrue($result);

        $tenant->refresh();
        $this->assertEquals('active', $tenant->status);
    }

    /**
     * Test branding configuration
     */
    public function test_update_branding()
    {
        $tenant = Tenant::create([
            'name' => 'Branding Test',
            'subdomain' => 'branding-test',
            'plan' => 'enterprise',
            'status' => 'active',
            'features' => json_encode(['custom_branding']),
            'limits' => json_encode(['users' => 1000, 'courses' => 100]),
            'usage' => json_encode(['users' => 0, 'courses' => 0]),
            'branding' => json_encode([]),
            'billing_info' => json_encode([]),
            'locale' => 'en',
            'timezone' => 'UTC',
        ]);

        $brandingData = [
            'logo_url' => 'https://example.com/logo.png',
            'primary_color' => '#3B82F6',
            'secondary_color' => '#10B981',
            'font_family' => 'Inter',
            'custom_css' => '.custom { color: red; }',
        ];

        $result = $this->tenantService->updateBranding($tenant->id, $brandingData);
        $this->assertTrue($result);

        $tenant->refresh();
        $branding = json_decode($tenant->branding, true);

        $this->assertEquals('https://example.com/logo.png', $branding['logo_url']);
        $this->assertEquals('#3B82F6', $branding['primary_color']);
        $this->assertEquals('#10B981', $branding['secondary_color']);
        $this->assertEquals('Inter', $branding['font_family']);
        $this->assertEquals('.custom { color: red; }', $branding['custom_css']);
    }

    /**
     * Test edge cases and error handling
     */
    public function test_edge_cases()
    {
        // Test creating tenant with invalid data
        $invalidData = [
            'name' => '',
            'subdomain' => 'invalid_subdomain',
            'admin_email' => 'invalid-email',
            'plan' => 'nonexistent_plan',
        ];

        $tenant = $this->tenantService->createTenant($invalidData);
        $this->assertNull($tenant);

        // Test operations on non-existent tenant
        $this->assertFalse($this->tenantService->hasFeature(99999, 'gamification'));
        $this->assertFalse($this->tenantService->canAddUsers(99999, 1));
        $this->assertFalse($this->tenantService->changePlan(99999, 'premium'));

        // Test updating usage for non-existent tenant
        $result = $this->tenantService->updateUsage(99999);
        $this->assertFalse($result);

        // Test getting statistics for non-existent tenant
        $stats = $this->tenantService->getTenantStatistics(99999);
        $this->assertNull($stats);
    }

    /**
     * Test trial expiration handling
     */
    public function test_trial_expiration()
    {
        $tenant = Tenant::create([
            'name' => 'Trial Test',
            'subdomain' => 'trial-test',
            'plan' => 'trial',
            'status' => 'active',
            'trial_ends_at' => now()->subDays(1), // Expired trial
            'features' => json_encode(['basic_features']),
            'limits' => json_encode(['users' => 10, 'courses' => 3]),
            'usage' => json_encode(['users' => 5, 'courses' => 2]),
            'branding' => json_encode([]),
            'billing_info' => json_encode([]),
            'locale' => 'en',
            'timezone' => 'UTC',
        ]);

        // Check if trial is expired
        $this->assertTrue($this->tenantService->isTrialExpired($tenant->id));

        // Active tenant with future trial end date
        $activeTenant = Tenant::create([
            'name' => 'Active Trial',
            'subdomain' => 'active-trial',
            'plan' => 'trial',
            'status' => 'active',
            'trial_ends_at' => now()->addDays(15),
            'features' => json_encode(['basic_features']),
            'limits' => json_encode(['users' => 10, 'courses' => 3]),
            'usage' => json_encode(['users' => 0, 'courses' => 0]),
            'branding' => json_encode([]),
            'billing_info' => json_encode([]),
            'locale' => 'en',
            'timezone' => 'UTC',
        ]);

        $this->assertFalse($this->tenantService->isTrialExpired($activeTenant->id));
    }
}