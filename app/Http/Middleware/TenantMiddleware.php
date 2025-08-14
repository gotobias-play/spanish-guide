<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the host from the request
        $host = $request->getHost();
        
        // Skip tenant resolution for localhost and IP addresses (development)
        if ($this->isDevelopmentHost($host)) {
            return $next($request);
        }

        // Extract subdomain or use domain
        $tenant = $this->resolveTenant($host);

        if (!$tenant) {
            // Return 404 if tenant not found
            abort(404, 'Tenant not found');
        }

        // Check if tenant is active
        if (!$tenant->isActive() && !$tenant->isTrial()) {
            abort(403, 'Tenant account is suspended or inactive');
        }

        // Check if trial has expired
        if ($tenant->isExpired()) {
            abort(403, 'Trial period has expired');
        }

        // Set the current tenant in the application
        app()->instance('tenant', $tenant);
        
        // Store tenant ID in config for global access
        Config::set('app.tenant_id', $tenant->id);
        Config::set('app.tenant', $tenant);

        // Set tenant-specific configurations
        $this->configureTenantSettings($tenant);

        return $next($request);
    }

    /**
     * Check if the host is a development environment
     */
    private function isDevelopmentHost(string $host): bool
    {
        return in_array($host, [
            'localhost',
            '127.0.0.1',
            'spanish.test',
            'spanish.local'
        ]) || filter_var($host, FILTER_VALIDATE_IP);
    }

    /**
     * Resolve tenant from host
     */
    private function resolveTenant(string $host): ?Tenant
    {
        // Check if tenants table exists (for migration safety)
        if (!Schema::hasTable('tenants')) {
            return null;
        }

        // Try to find tenant by exact domain first
        $tenant = Tenant::where('domain', $host)->first();
        
        if (!$tenant) {
            // Extract subdomain (e.g., "school1.spanishlearning.com" -> "school1")
            $parts = explode('.', $host);
            if (count($parts) >= 2) {
                $subdomain = $parts[0];
                $tenant = Tenant::where('subdomain', $subdomain)->first();
            }
        }

        return $tenant;
    }

    /**
     * Configure tenant-specific settings
     */
    private function configureTenantSettings(Tenant $tenant): void
    {
        // Set tenant timezone
        if ($tenant->timezone) {
            Config::set('app.timezone', $tenant->timezone);
            date_default_timezone_set($tenant->timezone);
        }

        // Set tenant locale
        if ($tenant->locale) {
            Config::set('app.locale', $tenant->locale);
            app()->setLocale($tenant->locale);
        }

        // Set tenant-specific app name
        Config::set('app.name', $tenant->name);

        // Store tenant features for easy access
        Config::set('tenant.features', $tenant->features ?? []);
        
        // Store tenant limits
        Config::set('tenant.limits', [
            'max_users' => $tenant->max_users,
            'max_courses' => $tenant->max_courses,
        ]);

        // Store tenant branding
        Config::set('tenant.branding', [
            'logo_url' => $tenant->logo_url,
            'primary_color' => $tenant->primary_color,
            'secondary_color' => $tenant->secondary_color,
        ]);
    }
}
