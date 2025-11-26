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

    // Subscription Plans - View Available Plans (Agent/Customer can see)
    Route::get('/subscription-plans', [\App\Http\Controllers\Api\SubscriptionPlanController::class, 'index']);
    Route::get('/subscription-plans/{id}', [\App\Http\Controllers\Api\SubscriptionPlanController::class, 'show']);
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
    Route::post('/properties/{id}/feature', [\App\Http\Controllers\Api\Admin\PropertyController::class, 'markFeatured']);
    Route::post('/properties/{id}/unfeature', [\App\Http\Controllers\Api\Admin\PropertyController::class, 'unmarkFeatured']);

    // User Management
    Route::post('/users/{userId}/deactivate', [\App\Http\Controllers\Api\Admin\UserManagementController::class, 'deactivate']);
    Route::post('/users/{userId}/activate', [\App\Http\Controllers\Api\Admin\UserManagementController::class, 'activate']);
    Route::get('/users/{userId}/status', [\App\Http\Controllers\Api\Admin\UserManagementController::class, 'status']);
    Route::get('/users', [\App\Http\Controllers\Api\Admin\UserManagementController::class, 'index']);
    Route::get('/export-users', [\App\Http\Controllers\Api\Admin\UserManagementController::class, 'export']);

    // Subscription Plans Management (Admin Only)
    Route::get('/subscription-plans', [\App\Http\Controllers\Api\Admin\SubscriptionPlanController::class, 'index']);
    Route::post('/subscription-plans', [\App\Http\Controllers\Api\Admin\SubscriptionPlanController::class, 'store']);
    Route::get('/subscription-plans/{id}', [\App\Http\Controllers\Api\Admin\SubscriptionPlanController::class, 'show']);
    Route::put('/subscription-plans/{id}', [\App\Http\Controllers\Api\Admin\SubscriptionPlanController::class, 'update']);
    Route::delete('/subscription-plans/{id}', [\App\Http\Controllers\Api\Admin\SubscriptionPlanController::class, 'destroy']);
    Route::post('/subscription-plans/{id}/toggle-status', [\App\Http\Controllers\Api\Admin\SubscriptionPlanController::class, 'toggleStatus']);
});

// Agent routes
Route::middleware(['auth:sanctum', 'agent'])->prefix('agent')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Api\Agent\DashboardController::class, 'index']);
    Route::get('/customers', [\App\Http\Controllers\Api\Admin\CustomerController::class, 'index']);

    
    // Check property limits
    Route::get('/properties/check-limits', [\App\Http\Controllers\Api\Agent\PropertyController::class, 'checkLimits']);
    
    // Property management
    Route::get('/properties', [\App\Http\Controllers\Api\Agent\PropertyController::class, 'index']);
    Route::post('/properties', [\App\Http\Controllers\Api\Agent\PropertyController::class, 'store']);
    Route::get('/properties/{id}', [\App\Http\Controllers\Api\Agent\PropertyController::class, 'show']);
    Route::put('/properties/{id}', [\App\Http\Controllers\Api\Agent\PropertyController::class, 'update']);
    Route::delete('/properties/{id}', [\App\Http\Controllers\Api\Agent\PropertyController::class, 'destroy']);
    Route::delete('/properties/{id}/video', [\App\Http\Controllers\Api\Agent\PropertyController::class, 'deleteVideo']);
    Route::get('/properties/{id}/analytics', [\App\Http\Controllers\Api\Agent\PropertyController::class, 'analytics']);
    
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

    // Appointments
    Route::get('/appointments', [\App\Http\Controllers\Api\Agent\AppointmentController::class, 'index']);
    Route::post('/appointments', [\App\Http\Controllers\Api\Agent\AppointmentController::class, 'store']);
    Route::get('/appointments/{id}', [\App\Http\Controllers\Api\Agent\AppointmentController::class, 'show']);
    Route::put('/appointments/{id}', [\App\Http\Controllers\Api\Agent\AppointmentController::class, 'update']);
    Route::post('/appointments/{id}/confirm', [\App\Http\Controllers\Api\Agent\AppointmentController::class, 'confirm']);
    Route::post('/appointments/{id}/complete', [\App\Http\Controllers\Api\Agent\AppointmentController::class, 'complete']);
    Route::post('/appointments/{id}/cancel', [\App\Http\Controllers\Api\Agent\AppointmentController::class, 'cancel']);
    Route::get('/appointments/availability/check', [\App\Http\Controllers\Api\Agent\AppointmentController::class, 'availability']);

    // Subscription info
    Route::get('/subscription/info', [\App\Http\Controllers\Api\Agent\PropertyController::class, 'subscriptionInfo']);
    
    // Featured property management
    Route::post('/properties/{id}/mark-featured', [\App\Http\Controllers\Api\Agent\PropertyController::class, 'markAsFeatured']);
    Route::post('/properties/{id}/remove-featured', [\App\Http\Controllers\Api\Agent\PropertyController::class, 'removeFeatured']);

    // Customer APIs for dropdown/selection
    Route::get('/customers/all', [\App\Http\Controllers\Api\Agent\CustomerController::class, 'getAllCustomers']);
    Route::get('/customers/my', [\App\Http\Controllers\Api\Agent\CustomerController::class, 'getMyCustomers']);
    Route::get('/customers/{customerId}/appointments', [\App\Http\Controllers\Api\Agent\CustomerController::class, 'getCustomerAppointments']);
    Route::get('/customers/{customerId}/inquiries', [\App\Http\Controllers\Api\Agent\CustomerController::class, 'getCustomerInquiries']);
    Route::get('/customers/{customerId}/properties', [\App\Http\Controllers\Api\Agent\CustomerController::class, 'getCustomerProperties']);
    Route::get('/customers/{customerId}/details', [\App\Http\Controllers\Api\Agent\CustomerController::class, 'getCustomerDetails']);

    // Reminders
    Route::get('/reminders', [\App\Http\Controllers\Api\Agent\ReminderController::class, 'index']);
    Route::post('/reminders', [\App\Http\Controllers\Api\Agent\ReminderController::class, 'store']);
    Route::get('/reminders/summary', [\App\Http\Controllers\Api\Agent\ReminderController::class, 'summary']);
    Route::get('/reminders/{id}', [\App\Http\Controllers\Api\Agent\ReminderController::class, 'show']);
    Route::put('/reminders/{id}', [\App\Http\Controllers\Api\Agent\ReminderController::class, 'update']);
    Route::post('/reminders/{id}/complete', [\App\Http\Controllers\Api\Agent\ReminderController::class, 'complete']);
    Route::post('/reminders/{id}/snooze', [\App\Http\Controllers\Api\Agent\ReminderController::class, 'snooze']);
    Route::post('/reminders/{id}/cancel', [\App\Http\Controllers\Api\Agent\ReminderController::class, 'cancel']);
    Route::delete('/reminders/{id}', [\App\Http\Controllers\Api\Agent\ReminderController::class, 'destroy']);
    
    // Quick create reminders
    Route::post('/inquiries/{inquiryId}/create-reminder', [\App\Http\Controllers\Api\Agent\ReminderController::class, 'createFromInquiry']);
    Route::post('/appointments/{appointmentId}/create-reminder', [\App\Http\Controllers\Api\Agent\ReminderController::class, 'createFromAppointment']);

    // Subscription status check
    Route::get('/subscription/status', [\App\Http\Controllers\Api\Agent\PropertyController::class, 'checkSubscriptionStatus']);
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

    // Appointments
    Route::get('/appointments', [\App\Http\Controllers\Api\Customer\AppointmentController::class, 'index']);
    Route::post('/appointments', [\App\Http\Controllers\Api\Customer\AppointmentController::class, 'store']);
    Route::get('/appointments/{id}', [\App\Http\Controllers\Api\Customer\AppointmentController::class, 'show']);
    Route::post('/appointments/{id}/cancel', [\App\Http\Controllers\Api\Customer\AppointmentController::class, 'cancel']);
    Route::get('/properties/{propertyId}/availability', [\App\Http\Controllers\Api\Customer\AppointmentController::class, 'checkAvailability']);
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

// Payment Routes (Authenticated users)
Route::middleware(['auth:sanctum'])->group(function () {
    // Production endpoints (for frontend)
    Route::post('/payments/create-intent', [\App\Http\Controllers\Api\PaymentController::class, 'createPaymentIntent']);
    Route::post('/payments/verify', [\App\Http\Controllers\Api\PaymentController::class, 'verifyPayment']);
    
    // Testing endpoints (backend only - auto-confirm)
    Route::post('/payments/create-and-confirm', [\App\Http\Controllers\Api\PaymentController::class, 'createAndConfirmPayment']);
    Route::post('/payments/confirm', [\App\Http\Controllers\Api\PaymentController::class, 'confirmPayment']);
    
    // Check payment status
    Route::post('/payments/check-status', [\App\Http\Controllers\Api\PaymentController::class, 'checkPaymentStatus']);
    
    // Subscriptions
    Route::get('/subscriptions/my', [\App\Http\Controllers\Api\PaymentController::class, 'mySubscriptions']);
    Route::get('/subscriptions/active', [\App\Http\Controllers\Api\PaymentController::class, 'activeSubscription']);
    Route::post('/subscriptions/{id}/cancel', [\App\Http\Controllers\Api\PaymentController::class, 'cancelSubscription']);
    
    // Payment history
    Route::get('/payments/history', [\App\Http\Controllers\Api\PaymentController::class, 'paymentHistory']);

    // Invoice routes
    Route::get('/payments/{paymentId}/invoice/download', [\App\Http\Controllers\Api\PaymentController::class, 'downloadInvoice']);
    Route::get('/payments/{paymentId}/invoice/view', [\App\Http\Controllers\Api\PaymentController::class, 'viewInvoice']);
    Route::post('/payments/{paymentId}/invoice/email', [\App\Http\Controllers\Api\PaymentController::class, 'emailInvoice']);

    // / ========== TESTING ENDPOINTS (Backend Only) ==========
    Route::post('/payments/test/create', [\App\Http\Controllers\Api\PaymentController::class, 'testCreateIntent']);
    Route::post('/payments/test/confirm', [\App\Http\Controllers\Api\PaymentController::class, 'testConfirmIntent']);
});

// Notifications (for all authenticated users)
Route::middleware(['auth:sanctum'])->group(function () {
    // Get notifications
    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::get('/notifications/unread', [\App\Http\Controllers\Api\NotificationController::class, 'unread']);
    Route::get('/notifications/count', [\App\Http\Controllers\Api\NotificationController::class, 'count']);
    
    // Mark as read
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
    
    // Delete notifications
    Route::delete('/notifications/{id}', [\App\Http\Controllers\Api\NotificationController::class, 'destroy']);
    Route::delete('/notifications/delete-all-read', [\App\Http\Controllers\Api\NotificationController::class, 'deleteAllRead']);
});