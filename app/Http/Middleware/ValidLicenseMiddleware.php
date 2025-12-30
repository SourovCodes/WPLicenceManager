<?php

namespace App\Http\Middleware;

use App\Models\License;
use App\Models\Product;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidLicenseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Validates that the request has a valid, active license for a product
     * that has API access enabled.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $licenseKey = $request->header('X-License-Key') ?? $request->input('license_key');
        $domain = $request->header('X-Domain') ?? $request->input('domain');
        $productSlug = $request->header('X-Product-Slug') ?? $request->input('product_slug');

        if (! $licenseKey || ! $domain || ! $productSlug) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required license credentials.',
                'required' => [
                    'license_key' => 'License key is required (header: X-License-Key or body: license_key)',
                    'domain' => 'Domain is required (header: X-Domain or body: domain)',
                    'product_slug' => 'Product slug is required (header: X-Product-Slug or body: product_slug)',
                ],
            ], 401);
        }

        $product = Product::where('slug', $productSlug)->first();

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        $license = License::with(['product', 'currentActivation'])
            ->where('license_key', $licenseKey)
            ->where('product_id', $product->id)
            ->first();

        if (! $license) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid license key.',
            ], 401);
        }

        if (! $license->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'License is not valid.',
                'status' => $license->status,
                'expires_at' => $license->expires_at?->toIso8601String(),
            ], 403);
        }

        $currentActivation = $license->currentActivation;
        if (! $currentActivation || $currentActivation->domain !== $domain) {
            return response()->json([
                'success' => false,
                'message' => 'License is not activated on this domain.',
            ], 403);
        }

        if (! $license->product->has_api_access) {
            return response()->json([
                'success' => false,
                'message' => 'This product does not include API access.',
            ], 403);
        }

        // Attach the license to the request for use in controllers
        $request->attributes->set('license', $license);

        return $next($request);
    }
}
