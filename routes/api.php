<?php

use App\Http\Controllers\Api\LicenseController;
use App\Http\Controllers\Api\PremiumApiController;
use App\Http\Controllers\Api\UpdateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
|
*/

// License Management Routes (Public - no auth required)
Route::prefix('license')->group(function () {
    Route::post('/activate', [LicenseController::class, 'activate']);
    Route::post('/deactivate', [LicenseController::class, 'deactivate']);
    Route::post('/validate', [LicenseController::class, 'validate']);
    Route::post('/status', [LicenseController::class, 'status']);
});

// Update Routes (License validated within controller)
Route::prefix('update')->group(function () {
    Route::post('/check', [UpdateController::class, 'check']);
    Route::post('/download', [UpdateController::class, 'download']);
});

// Premium API Routes (Protected by valid license middleware)
// Only accessible with valid license + product has_api_access = true
Route::prefix('premium')->middleware('valid.license')->group(function () {
    Route::post('/upload-products', [PremiumApiController::class, 'uploadProducts']);
    Route::get('/upload-status/{uploadId}', [PremiumApiController::class, 'uploadStatus']);
    Route::get('/upload-history', [PremiumApiController::class, 'uploadHistory']);
    Route::post('/check-sftp', [PremiumApiController::class, 'checkSftpCredentials']);
});
