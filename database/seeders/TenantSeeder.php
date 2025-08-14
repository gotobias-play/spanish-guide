<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Services\TenantService;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenantService = app(TenantService::class);

        // Create sample tenants for demonstration
        $tenants = [
            [
                'name' => 'Madrid Language Academy',
                'subdomain' => 'madrid-academy',
                'contact_email' => 'admin@madrid-academy.com',
                'contact_phone' => '+34 91 123 4567',
                'description' => 'Premier English language academy in Madrid serving students of all levels.',
                'plan' => 'premium',
                'timezone' => 'Europe/Madrid',
                'locale' => 'es',
                'primary_color' => '#E74C3C',
                'secondary_color' => '#F39C12',
                'admin_user' => [
                    'name' => 'Carlos Rodriguez',
                    'email' => 'carlos@madrid-academy.com',
                    'password' => 'MadridAcademy2025!'
                ]
            ],
            [
                'name' => 'Barcelona International School',
                'subdomain' => 'barcelona-school',
                'contact_email' => 'director@barcelona-school.edu',
                'contact_phone' => '+34 93 987 6543',
                'description' => 'International school providing comprehensive English education programs.',
                'plan' => 'enterprise',
                'timezone' => 'Europe/Madrid',
                'locale' => 'es',
                'primary_color' => '#3498DB',
                'secondary_color' => '#9B59B6',
                'admin_user' => [
                    'name' => 'Maria Gonzalez',
                    'email' => 'maria@barcelona-school.edu',
                    'password' => 'BarcelonaSchool2025!'
                ]
            ],
            [
                'name' => 'Valencia English Center',
                'subdomain' => 'valencia-center',
                'contact_email' => 'info@valencia-center.es',
                'contact_phone' => '+34 96 555 1234',
                'description' => 'Modern English learning center focusing on conversational skills and practical application.',
                'plan' => 'basic',
                'timezone' => 'Europe/Madrid',
                'locale' => 'es',
                'primary_color' => '#27AE60',
                'secondary_color' => '#E67E22',
                'admin_user' => [
                    'name' => 'Antonio Martinez',
                    'email' => 'antonio@valencia-center.es',
                    'password' => 'ValenciaCenter2025!'
                ]
            ],
            [
                'name' => 'Demo Trial School',
                'subdomain' => 'demo-trial',
                'contact_email' => 'demo@trial-school.com',
                'description' => 'Demo school for testing trial features and functionality.',
                'plan' => 'trial',
                'timezone' => 'UTC',
                'locale' => 'en',
                'primary_color' => '#8E44AD',
                'secondary_color' => '#E74C3C',
                'admin_user' => [
                    'name' => 'Demo Administrator',
                    'email' => 'admin@trial-school.com',
                    'password' => 'DemoTrial2025!'
                ]
            ]
        ];

        foreach ($tenants as $tenantData) {
            $this->command->info("Creating tenant: {$tenantData['name']}");
            
            try {
                $tenant = $tenantService->createTenant($tenantData);
                $this->command->info("✓ Created tenant '{$tenant->name}' with subdomain '{$tenant->subdomain}'");
                $this->command->info("  Access URL: https://{$tenant->subdomain}.yourdomain.com");
                $this->command->info("  Admin: {$tenantData['admin_user']['email']} / {$tenantData['admin_user']['password']}");
                $this->command->line('');
            } catch (\Exception $e) {
                $this->command->error("✗ Failed to create tenant '{$tenantData['name']}': {$e->getMessage()}");
            }
        }

        $this->command->info('Tenant seeding completed!');
        $this->command->line('');
        $this->command->info('Test the multi-tenant system by:');
        $this->command->info('1. Setting up local domains in your hosts file:');
        $this->command->info('   127.0.0.1 madrid-academy.localhost');
        $this->command->info('   127.0.0.1 barcelona-school.localhost');
        $this->command->info('   127.0.0.1 valencia-center.localhost');
        $this->command->info('   127.0.0.1 demo-trial.localhost');
        $this->command->line('');
        $this->command->info('2. Or modify the TenantMiddleware to allow subdomain testing on localhost');
    }
}
