<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Premium\UploadProductsRequest;
use App\Jobs\UploadCsvToSftp;
use App\Models\CsvUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PremiumApiController extends Controller
{
    /**
     * Upload a CSV file to be transferred to an SFTP server.
     *
     * This endpoint accepts a CSV file along with SFTP credentials.
     * The file is stored locally and a job is queued to upload it
     * to the specified SFTP server (Nalda).
     */
    public function uploadProducts(UploadProductsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $license = $request->attributes->get('license');

        $file = $request->file('csv_file');
        $originalFilename = $file->getClientOriginalName();

        $storedPath = $file->store('csv-uploads/'.$license->id, 'local');

        $csvUpload = CsvUpload::create([
            'license_id' => $license->id,
            'original_filename' => $originalFilename,
            'stored_path' => $storedPath,
            'sftp_host' => $validated['sftp_host'],
            'sftp_port' => $validated['sftp_port'] ?? 22,
            'sftp_username' => $validated['sftp_username'],
            'sftp_password' => $validated['sftp_password'],
            'sftp_remote_path' => $validated['sftp_remote_path'] ?? null,
            'status' => 'pending',
        ]);

        UploadCsvToSftp::dispatch($csvUpload);

        return response()->json([
            'success' => true,
            'message' => 'CSV file uploaded successfully. Transfer to SFTP server has been queued.',
            'data' => [
                'upload_id' => $csvUpload->id,
                'filename' => $originalFilename,
                'status' => $csvUpload->status,
            ],
        ], 202);
    }

    /**
     * Get the status of a CSV upload.
     */
    public function uploadStatus(Request $request, int $uploadId): JsonResponse
    {
        $license = $request->attributes->get('license');

        $csvUpload = CsvUpload::where('id', $uploadId)
            ->where('license_id', $license->id)
            ->first();

        if (! $csvUpload) {
            return response()->json([
                'success' => false,
                'message' => 'Upload not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'upload_id' => $csvUpload->id,
                'filename' => $csvUpload->original_filename,
                'status' => $csvUpload->status,
                'error_message' => $csvUpload->error_message,
                'created_at' => $csvUpload->created_at->toIso8601String(),
                'processed_at' => $csvUpload->processed_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get all uploads for the current license.
     */
    public function uploadHistory(Request $request): JsonResponse
    {
        $license = $request->attributes->get('license');

        $uploads = CsvUpload::where('license_id', $license->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['id', 'original_filename', 'status', 'error_message', 'created_at', 'processed_at']);

        return response()->json([
            'success' => true,
            'data' => $uploads->map(fn (CsvUpload $upload) => [
                'upload_id' => $upload->id,
                'filename' => $upload->original_filename,
                'status' => $upload->status,
                'error_message' => $upload->error_message,
                'created_at' => $upload->created_at->toIso8601String(),
                'processed_at' => $upload->processed_at?->toIso8601String(),
            ]),
        ]);
    }
}
