<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TenantController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Get current tenant information
     */
    public function current(Request $request): JsonResponse
    {
        $tenant = app('tenant');
        
        if (!$tenant) {
            return response()->json([
                'message' => 'No tenant context found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'subdomain' => $tenant->subdomain,
                    'domain' => $tenant->domain,
                    'logo_url' => $tenant->logo_url,
                    'primary_color' => $tenant->primary_color,
                    'secondary_color' => $tenant->secondary_color,
                    'plan' => $tenant->plan,
                    'status' => $tenant->status,
                    'locale' => $tenant->locale,
                    'allowed_locales' => $tenant->allowed_locales,
                    'timezone' => $tenant->timezone,
                    'features' => $tenant->features,
                    'trial_ends_at' => $tenant->trial_ends_at,
                    'remaining_trial_days' => $tenant->getRemainingTrialDays(),
                    'usage' => $tenant->getUsagePercentage(),
                ]
            ]
        ]);
    }

    /**
     * Get tenant statistics (admin only)
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user || !$user->is_admin) {
            return response()->json([
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $tenant = app('tenant');
        if (!$tenant) {
            return response()->json([
                'message' => 'No tenant context found'
            ], 404);
        }

        $stats = $this->tenantService->getTenantStats($tenant);

        return response()->json([
            'status' => 'success',
            'data' => [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'stats' => $stats
            ]
        ]);
    }

    /**
     * Update tenant settings (admin only)
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user || !$user->is_admin) {
            return response()->json([
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $tenant = app('tenant');
        if (!$tenant) {
            return response()->json([
                'message' => 'No tenant context found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'contact_email' => 'sometimes|email|max:255',
            'contact_phone' => 'sometimes|string|max:20',
            'logo_url' => 'sometimes|url|max:500',
            'primary_color' => 'sometimes|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'secondary_color' => 'sometimes|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'timezone' => 'sometimes|string|timezone',
            'locale' => 'sometimes|string|in:en,es',
            'allowed_locales' => 'sometimes|array',
            'allowed_locales.*' => 'string|in:en,es',
            'settings' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $validator->validated();
        $tenant->update($updateData);

        return response()->json([
            'status' => 'success',
            'message' => 'Tenant settings updated successfully',
            'data' => [
                'tenant' => $tenant->fresh()
            ]
        ]);
    }

    /**
     * Validate subdomain availability
     */
    public function validateSubdomain(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subdomain' => 'required|string|min:3|max:63'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $subdomain = $request->input('subdomain');
        $isValid = $this->tenantService->validateSubdomain($subdomain);

        return response()->json([
            'status' => 'success',
            'data' => [
                'subdomain' => $subdomain,
                'is_available' => $isValid,
                'suggestion' => !$isValid ? $this->tenantService->generateSubdomainSuggestion($subdomain) : null
            ]
        ]);
    }

    /**
     * Create new tenant (super admin only)
     */
    public function store(Request $request): JsonResponse
    {
        // This would typically be restricted to super admin users
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string|min:3|max:63|unique:tenants,subdomain',
            'domain' => 'sometimes|string|max:255|unique:tenants,domain',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'sometimes|string|max:20',
            'description' => 'sometimes|string|max:1000',
            'plan' => 'sometimes|string|in:trial,basic,premium,enterprise',
            'timezone' => 'sometimes|string|timezone',
            'locale' => 'sometimes|string|in:en,es',
            'admin_user' => 'required|array',
            'admin_user.name' => 'required|string|max:255',
            'admin_user.email' => 'required|email|max:255|unique:users,email',
            'admin_user.password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Additional subdomain validation
        if (!$this->tenantService->validateSubdomain($request->input('subdomain'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Subdomain is not available or invalid',
                'errors' => [
                    'subdomain' => ['The subdomain is not available or contains invalid characters']
                ]
            ], 422);
        }

        try {
            $tenant = $this->tenantService->createTenant($validator->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Tenant created successfully',
                'data' => [
                    'tenant' => $tenant,
                    'access_url' => "https://{$tenant->subdomain}.{$request->getHost()}"
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create tenant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tenant branding information (public)
     */
    public function branding(): JsonResponse
    {
        $tenant = app('tenant');
        
        if (!$tenant) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'branding' => [
                        'name' => config('app.name', 'Spanish English Learning'),
                        'logo_url' => null,
                        'primary_color' => '#4A55A2',
                        'secondary_color' => '#7209B7',
                    ]
                ]
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'branding' => [
                    'name' => $tenant->name,
                    'logo_url' => $tenant->logo_url,
                    'primary_color' => $tenant->primary_color,
                    'secondary_color' => $tenant->secondary_color,
                ]
            ]
        ]);
    }

    /**
     * Check feature availability
     */
    public function checkFeature(Request $request, string $feature): JsonResponse
    {
        $tenant = app('tenant');
        
        $hasFeature = $tenant ? $tenant->hasFeature($feature) : false;

        return response()->json([
            'status' => 'success',
            'data' => [
                'feature' => $feature,
                'available' => $hasFeature,
                'plan_required' => $this->getFeaturePlanRequirement($feature)
            ]
        ]);
    }

    /**
     * Get minimum plan required for a feature
     */
    private function getFeaturePlanRequirement(string $feature): string
    {
        return match ($feature) {
            'quizzes', 'progress_tracking', 'basic_analytics' => 'trial',
            'gamification', 'certificates' => 'basic',
            'advanced_analytics', 'social_features', 'ai_features' => 'premium',
            'custom_branding', 'api_access', 'sso', 'custom_domains' => 'enterprise',
            default => 'trial',
        };
    }
}
