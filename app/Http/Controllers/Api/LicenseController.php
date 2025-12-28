<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\License\ActivateLicenseRequest;
use App\Http\Requests\Api\License\DeactivateLicenseRequest;
use App\Http\Requests\Api\License\LicenseStatusRequest;
use App\Http\Requests\Api\License\ValidateLicenseRequest;
use App\Models\License;
use Illuminate\Http\JsonResponse;

class LicenseController extends Controller
{
    /**
     * Activate a license on a domain.
     */
    public function activate(ActivateLicenseRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $license = License::with('product')
            ->where('license_key', $validated['license_key'])
            ->whereHas('product', fn ($q) => $q->where('slug', $validated['product_slug']))
            ->first();

        if (! $license) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid license key or product.',
            ], 404);
        }

        if ($license->status === 'revoked') {
            return response()->json([
                'success' => false,
                'message' => 'This license has been revoked.',
            ], 403);
        }

        if ($license->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'This license has expired.',
                'expires_at' => $license->expires_at?->toIso8601String(),
            ], 403);
        }

        // Check if already activated on this domain
        $currentActivation = $license->currentActivation;
        if ($currentActivation && $currentActivation->domain === $validated['domain']) {
            return response()->json([
                'success' => true,
                'message' => 'License is already activated on this domain.',
                'license' => $this->formatLicenseResponse($license),
            ]);
        }

        // Check if switching domains is allowed
        if ($currentActivation && ! $license->canChangeDomain()) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum domain changes reached. Please contact support.',
                'domain_changes_used' => $license->domain_changes_used,
                'max_domain_changes' => $license->max_domain_changes,
            ], 403);
        }

        $activation = $license->activate(
            $validated['domain'],
            $request->ip()
        );

        $license->refresh();

        return response()->json([
            'success' => true,
            'message' => 'License activated successfully.',
            'license' => $this->formatLicenseResponse($license),
            'local_key' => $activation->local_key,
        ]);
    }

    /**
     * Deactivate a license from a domain.
     */
    public function deactivate(DeactivateLicenseRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $license = License::with('currentActivation')
            ->where('license_key', $validated['license_key'])
            ->whereHas('product', fn ($q) => $q->where('slug', $validated['product_slug']))
            ->first();

        if (! $license) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid license key or product.',
            ], 404);
        }

        $currentActivation = $license->currentActivation;
        if (! $currentActivation || $currentActivation->domain !== $validated['domain']) {
            return response()->json([
                'success' => false,
                'message' => 'License is not activated on this domain.',
            ], 400);
        }

        $license->deactivate('Deactivated via API');

        return response()->json([
            'success' => true,
            'message' => 'License deactivated successfully.',
        ]);
    }

    /**
     * Validate a license.
     */
    public function validate(ValidateLicenseRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $license = License::with(['product', 'currentActivation'])
            ->where('license_key', $validated['license_key'])
            ->whereHas('product', fn ($q) => $q->where('slug', $validated['product_slug']))
            ->first();

        if (! $license) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Invalid license key or product.',
            ], 404);
        }

        $isValid = $license->isValid()
            && $license->currentActivation
            && $license->currentActivation->domain === $validated['domain'];

        return response()->json([
            'success' => true,
            'valid' => $isValid,
            'message' => $isValid ? 'License is valid.' : 'License is not valid for this domain.',
            'license' => $this->formatLicenseResponse($license),
        ]);
    }

    /**
     * Get license status/info.
     */
    public function status(LicenseStatusRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $license = License::with(['product', 'currentActivation'])
            ->where('license_key', $validated['license_key'])
            ->whereHas('product', fn ($q) => $q->where('slug', $validated['product_slug']))
            ->first();

        if (! $license) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid license key or product.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'license' => $this->formatLicenseResponse($license),
        ]);
    }

    /**
     * Format license data for API response.
     *
     * @return array<string, mixed>
     */
    private function formatLicenseResponse(License $license): array
    {
        return [
            'status' => $license->status,
            'product' => [
                'name' => $license->product->name,
                'slug' => $license->product->slug,
                'version' => $license->product->version,
                'has_api_access' => $license->product->has_api_access,
            ],
            'activated_at' => $license->activated_at?->toIso8601String(),
            'expires_at' => $license->expires_at?->toIso8601String(),
            'is_expired' => $license->isExpired(),
            'active_domain' => $license->getActiveDomain(),
            'domain_changes_remaining' => $license->remainingDomainChanges(),
        ];
    }
}
