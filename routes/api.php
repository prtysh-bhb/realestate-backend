<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TwoFactorController;
use App\Http\Controllers\Api\PasswordResetController;

// Password Reset routes (public)
Route::post('/password/email', [PasswordResetController::class, 'sendResetLink']);
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);
Route::post('/password/verify-token', [PasswordResetController::class, 'verifyToken']);

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-login', [AuthController::class, 'verifyLogin']);

// Protected routes (any authenticated user)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // 2FA routes
    Route::post('/2fa/setup', [TwoFactorController::class, 'setup']);
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable']);
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable']);
    Route::post('/2fa/verify', [TwoFactorController::class, 'verify']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Api\Admin\DashboardController::class, 'index']);
    
    // Agent management
    Route::get('/agents', [\App\Http\Controllers\Api\Admin\AgentController::class, 'index']);
    Route::get('/agents/{id}', [\App\Http\Controllers\Api\Admin\AgentController::class, 'show']);
    
    // Customer management
    Route::get('/customers', [\App\Http\Controllers\Api\Admin\CustomerController::class, 'index']);
    Route::get('/customers/{id}', [\App\Http\Controllers\Api\Admin\CustomerController::class, 'show']);
    
    // Property management
    Route::get('/properties', [\App\Http\Controllers\Api\Admin\PropertyController::class, 'index']);
    Route::get('/properties/statistics', [\App\Http\Controllers\Api\Admin\PropertyController::class, 'statistics']);
    Route::get('/properties/{id}', [\App\Http\Controllers\Api\Admin\PropertyController::class, 'show']);
    Route::post('/properties/{id}/approve', [\App\Http\Controllers\Api\Admin\PropertyController::class, 'approve']);
    Route::post('/properties/{id}/reject', [\App\Http\Controllers\Api\Admin\PropertyController::class, 'reject']);
    Route::put('/properties/{id}/status', [\App\Http\Controllers\Api\Admin\PropertyController::class, 'updateStatus']);

    // User Management
    Route::post('/users/{userId}/deactivate', [\App\Http\Controllers\Api\Admin\UserManagementController::class, 'deactivate']);
    Route::post('/users/{userId}/activate', [\App\Http\Controllers\Api\Admin\UserManagementController::class, 'activate']);
    Route::get('/users/{userId}/status', [\App\Http\Controllers\Api\Admin\UserManagementController::class, 'status']);
    Route::get('/users', [\App\Http\Controllers\Api\Admin\UserManagementController::class, 'index']);
    Route::get('/export-users', [\App\Http\Controllers\Api\Admin\UserManagementController::class, 'export']);
});

// Agent routes
Route::middleware(['auth:sanctum', 'agent'])->prefix('agent')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Api\Agent\DashboardController::class, 'index']);
    
    // Property management
    Route::get('/properties', [\App\Http\Controllers\Api\Agent\PropertyController::class, 'index']);
    Route::post('/properties', [\App\Http\Controllers\Api\Agent\PropertyController::class, 'store']);
    Route::get('/properties/{id}', [\App\Http\Controllers\Api\Agent\PropertyController::class, 'show']);
    Route::put('/properties/{id}', [\App\Http\Controllers\Api\Agent\PropertyController::class, 'update']);
    Route::delete('/properties/{id}', [\App\Http\Controllers\Api\Agent\PropertyController::class, 'destroy']);
    Route::delete('/properties/{id}/video', [\App\Http\Controllers\Api\Agent\PropertyController::class, 'deleteVideo']);
    
    // Inquiries
    Route::get('/inquiries', [\App\Http\Controllers\Api\Agent\InquiryController::class, 'index']);
    Route::get('/inquiries/{id}', [\App\Http\Controllers\Api\Agent\InquiryController::class, 'show']);
    Route::put('/inquiries/{id}/status', [\App\Http\Controllers\Api\Agent\InquiryController::class, 'updateStatus']);

    // Property Images
    Route::get('/properties/{propertyId}/images', [\App\Http\Controllers\Api\Agent\PropertyImageController::class, 'index']);
    Route::post('/properties/{propertyId}/images/single', [\App\Http\Controllers\Api\Agent\PropertyImageController::class, 'uploadSingle']);
    Route::post('/properties/{propertyId}/images/multiple', [\App\Http\Controllers\Api\Agent\PropertyImageController::class, 'uploadMultiple']);
    Route::delete('/properties/{propertyId}/images/{imageId}', [\App\Http\Controllers\Api\Agent\PropertyImageController::class, 'destroy']);
    Route::put('/properties/{propertyId}/images/{imageId}/primary', [\App\Http\Controllers\Api\Agent\PropertyImageController::class, 'setPrimary']);
    Route::post('/properties/{propertyId}/images/reorder', [\App\Http\Controllers\Api\Agent\PropertyImageController::class, 'reorder']);

    // Property Documents
    Route::post('/properties/{propertyId}/documents', [\App\Http\Controllers\Api\Agent\PropertyDocumentController::class, 'upload']);
    Route::get('/properties/{propertyId}/documents', [\App\Http\Controllers\Api\Agent\PropertyDocumentController::class, 'index']);
    Route::delete('/properties/{propertyId}/documents', [\App\Http\Controllers\Api\Agent\PropertyDocumentController::class, 'destroy']);

    // Lead Management
    Route::put('/inquiries/{id}/stage', [\App\Http\Controllers\Api\Agent\InquiryController::class, 'updateStage']);
    Route::post('/inquiries/{id}/notes', [\App\Http\Controllers\Api\Agent\InquiryController::class, 'addNote']);
    Route::get('/inquiries/{id}/history', [\App\Http\Controllers\Api\Agent\InquiryController::class, 'history']);
});

// Customer routes
Route::middleware(['auth:sanctum', 'customer'])->prefix('customer')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Api\Customer\DashboardController::class, 'index']);
    
    // Favorites
    Route::get('/favorites', [\App\Http\Controllers\Api\Customer\FavoriteController::class, 'index']);
    Route::post('/favorites/{propertyId}', [\App\Http\Controllers\Api\Customer\FavoriteController::class, 'store']);
    Route::delete('/favorites/{propertyId}', [\App\Http\Controllers\Api\Customer\FavoriteController::class, 'destroy']);
    Route::get('/favorites/check/{propertyId}', [\App\Http\Controllers\Api\Customer\FavoriteController::class, 'check']);
    
    // Inquiries
    Route::get('/inquiries', [\App\Http\Controllers\Api\Customer\InquiryController::class, 'index']);
    Route::post('/inquiries/{propertyId}', [\App\Http\Controllers\Api\Customer\InquiryController::class, 'store']);
    Route::get('/inquiries/{id}', [\App\Http\Controllers\Api\Customer\InquiryController::class, 'show']);
});

// Public property routes - Use FULL namespace
Route::get('/properties', [\App\Http\Controllers\Api\PropertyController::class, 'index']);
Route::get('/properties/search', [\App\Http\Controllers\Api\PropertyController::class, 'search']);
Route::get('/properties/{id}', [\App\Http\Controllers\Api\PropertyController::class, 'show']);
Route::get('/amenities', [\App\Http\Controllers\Api\AmenitiesController::class, 'index']);

// Profile routes (for all authenticated users)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\Api\ProfileController::class, 'show']);
    Route::put('/profile', [\App\Http\Controllers\Api\ProfileController::class, 'update']);
    Route::put('/profile/password', [\App\Http\Controllers\Api\ProfileController::class, 'changePassword']);
    Route::post('/profile/avatar', [\App\Http\Controllers\Api\ProfileController::class, 'uploadAvatar']);
    Route::delete('/profile/avatar', [\App\Http\Controllers\Api\ProfileController::class, 'deleteAvatar']);
    Route::delete('/profile/account', [\App\Http\Controllers\Api\ProfileController::class, 'deleteAccount']);
});