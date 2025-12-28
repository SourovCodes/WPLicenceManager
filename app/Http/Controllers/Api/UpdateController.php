<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Update\CheckUpdateRequest;
use App\Http\Requests\Api\Update\DownloadUpdateRequest;
use App\Models\License;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class UpdateController extends Controller
{
    /**
     * Check for updates for a product.
     */
    public function check(CheckUpdateRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $product = Product::where('slug', $validated['product_slug'])
            ->where('is_active', true)
            ->first();

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        // If product doesn't require license, allow updates
        if (! $product->requires_license) {
            return $this->buildUpdateResponse($product, $validated['current_version']);
        }

        // Validate license for licensed products
        $license = License::with('currentActivation')
            ->where('license_key', $validated['license_key'])
            ->where('product_id', $product->id)
            ->first();

        if (! $license) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid license key.',
                'update_available' => false,
            ], 403);
        }

        if (! $license->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'License is not valid. Please renew your license.',
                'update_available' => false,
                'license_status' => $license->status,
                'expires_at' => $license->expires_at?->toIso8601String(),
            ], 403);
        }

        $currentActivation = $license->currentActivation;
        if (! $currentActivation || $currentActivation->domain !== $validated['domain']) {
            return response()->json([
                'success' => false,
                'message' => 'License is not activated on this domain.',
                'update_available' => false,
            ], 403);
        }

        return $this->buildUpdateResponse($product, $validated['current_version']);
    }

    /**
     * Download the update package.
     */
    public function download(DownloadUpdateRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $product = Product::where('slug', $validated['product_slug'])
            ->where('is_active', true)
            ->first();

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        if ($product->requires_license) {
            $license = License::with('currentActivation')
                ->where('license_key', $validated['license_key'])
                ->where('product_id', $product->id)
                ->first();

            if (! $license || ! $license->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Valid license required for download.',
                ], 403);
            }

            $currentActivation = $license->currentActivation;
            if (! $currentActivation || $currentActivation->domain !== $validated['domain']) {
                return response()->json([
                    'success' => false,
                    'message' => 'License is not activated on this domain.',
                ], 403);
            }
        }

        if (! $product->download_url) {
            return response()->json([
                'success' => false,
                'message' => 'Download not available.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'download_url' => $product->download_url,
            'version' => $product->version,
        ]);
    }

    /**
     * Build update response with version comparison.
     */
    private function buildUpdateResponse(Product $product, string $currentVersion): JsonResponse
    {
        $hasUpdate = version_compare($product->version, $currentVersion, '>');

        return response()->json([
            'success' => true,
            'update_available' => $hasUpdate,
            'current_version' => $currentVersion,
            'latest_version' => $product->version,
            'product' => [
                'name' => $product->name,
                'slug' => $product->slug,
            ],
        ]);
    }
}
