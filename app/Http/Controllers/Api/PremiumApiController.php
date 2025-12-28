<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Premium\ProcessActionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PremiumApiController extends Controller
{
    /**
     * Mock premium API endpoint - Example: Get premium data.
     *
     * This endpoint is protected by ValidLicenseMiddleware
     * and only accessible to products with has_api_access = true.
     */
    public function getData(Request $request): JsonResponse
    {
        // Access the validated license from the request (set by middleware)
        $license = $request->attributes->get('license');

        return response()->json([
            'success' => true,
            'message' => 'Premium API access granted.',
            'data' => [
                'premium_feature_1' => 'This is premium data only for licensed users.',
                'premium_feature_2' => [
                    'item_1' => 'Premium item 1',
                    'item_2' => 'Premium item 2',
                    'item_3' => 'Premium item 3',
                ],
                'timestamp' => now()->toIso8601String(),
                'license_info' => [
                    'product' => $license->product->name,
                    'expires_at' => $license->expires_at?->toIso8601String(),
                ],
            ],
        ]);
    }

    /**
     * Mock premium API endpoint - Example: Process premium action.
     */
    public function processAction(ProcessActionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $license = $request->attributes->get('license');

        // Mock processing based on action
        $result = match ($validated['action']) {
            'generate_report' => [
                'report_id' => 'RPT-'.strtoupper(substr(md5(time()), 0, 8)),
                'status' => 'generated',
                'download_url' => 'https://example.com/reports/mock-report.pdf',
            ],
            'sync_data' => [
                'sync_id' => 'SYN-'.strtoupper(substr(md5(time()), 0, 8)),
                'status' => 'completed',
                'records_synced' => rand(10, 100),
            ],
            'analyze' => [
                'analysis_id' => 'ANL-'.strtoupper(substr(md5(time()), 0, 8)),
                'status' => 'completed',
                'insights' => [
                    'metric_1' => rand(1, 100),
                    'metric_2' => rand(1, 100),
                    'recommendation' => 'This is a mock recommendation.',
                ],
            ],
            default => [
                'status' => 'processed',
                'action' => $validated['action'],
            ],
        };

        return response()->json([
            'success' => true,
            'message' => "Action '{$validated['action']}' processed successfully.",
            'result' => $result,
            'license_product' => $license->product->name,
        ]);
    }

    /**
     * Mock premium API endpoint - Example: Get premium settings/configuration.
     */
    public function getSettings(Request $request): JsonResponse
    {
        $license = $request->attributes->get('license');

        return response()->json([
            'success' => true,
            'settings' => [
                'feature_flags' => [
                    'advanced_analytics' => true,
                    'custom_branding' => true,
                    'priority_support' => true,
                    'api_rate_limit' => 1000,
                ],
                'integrations' => [
                    'webhook_enabled' => true,
                    'max_webhooks' => 10,
                ],
                'limits' => [
                    'max_requests_per_hour' => 5000,
                    'max_storage_mb' => 1024,
                ],
            ],
            'product' => $license->product->name,
        ]);
    }
}
