<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Organization/School name
            $table->string('subdomain')->unique(); // Unique subdomain identifier
            $table->string('domain')->nullable(); // Custom domain (optional)
            $table->string('logo_url')->nullable(); // Organization logo
            $table->string('primary_color')->default('#4A55A2'); // Brand primary color
            $table->string('secondary_color')->default('#7209B7'); // Brand secondary color
            $table->json('settings')->nullable(); // Flexible tenant-specific settings
            $table->json('features')->nullable(); // Enabled features for this tenant
            $table->string('plan')->default('basic'); // Subscription plan (basic, premium, enterprise)
            $table->string('status')->default('active'); // active, suspended, trial
            $table->timestamp('trial_ends_at')->nullable(); // Trial period end
            $table->integer('max_users')->default(50); // Maximum users allowed
            $table->integer('max_courses')->default(10); // Maximum courses allowed
            $table->string('contact_email')->nullable(); // Primary contact email
            $table->string('contact_phone')->nullable(); // Primary contact phone
            $table->text('description')->nullable(); // Organization description
            $table->string('timezone')->default('UTC'); // Tenant timezone
            $table->string('locale')->default('es'); // Default locale for tenant
            $table->json('allowed_locales')->default('["es", "en"]'); // Supported languages
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['subdomain']);
            $table->index(['domain']);
            $table->index(['status']);
            $table->index(['plan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
