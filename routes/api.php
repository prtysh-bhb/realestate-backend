<?php

use App\Http\Controllers\Api\Admin\BlogController;
use App\Http\Controllers\Api\Admin\FAQController;
use App\Http\Controllers\Api\Admin\NewsController;
use App\Http\Controllers\Api\Agent\MessageController;
use App\Http\Controllers\Api\Customer\AgentReviewController;
use App\Http\Controllers\Api\Customer\PropertyReviewController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SocialAuthController; 
use App\Http\Controllers\Api\TwoFactorController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\Admin\CreditController as AdminCreditController;
use App\Http\Controllers\Api\Admin\AppSettingController;
use App\Http\Controllers\Api\Admin\WalletController as AdminWalletController;
use App\Http\Controllers\Api\Customer\WalletController as CustomerWalletController;

// For authenticate broadcasting in API
Route::middleware('auth:sanctum')->post('/broadcasting/auth', function (Illuminate\Http\Request $request) {
    return \Illuminate\Support\Facades\Broadcast::auth($request);
});

// Password Reset routes (public)
Route::post('/password/email', [PasswordResetController::class, 'sendResetLink']);
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);
Route::post('/password/verify-token', [PasswordResetController::class, 'verifyToken']);

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-login', [AuthController::class, 'verifyLogin']);
// Credit Packages - Public pricing page
Route::get('/credit-packages', [\App\Http\Controllers\Api\CreditController::class, 'packages']);

// Social Login
Route::get('/auth/{provider}', [SocialAuthController::class, 'redirectToProvider']);
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback']);

// Contact Form
Route::post('/contact-form', [\App\Http\Controllers\Api\ContactFormController::class, 'submit']);
// Property Valuation
Route::post('/property-valuation', [\App\Http\Controllers\Api\ValuationController::class, 'calculate']);
// Loan Calculator
Route::post('/loan-eligibility', [\App\Http\Controllers\Api\LoanCalculatorController::class, 'calculate']);
// Contact Form
Route::post('/contact-form', [\App\Http\Controllers\Api\ContactFormController::class, 'submit']);
// Property Valuation
Route::post('/property-valuation', [\App\Http\Controllers\Api\ValuationController::class, 'calculate']);
// Loan Calculator
Route::post('/loan-eligibility', [\App\Http\Controllers\Api\LoanCalculatorController::class, 'calculate']);

Route::middleware('guest:sanctum')->group(function(){
    Route::get('/restricted-mail-domains', [AuthController::class, 'getRestrictedDomains']);
});

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

    // User Details
    Route::put('/users/{id}/profile', [\App\Http\Controllers\Api\Admin\UserManagementController::class, 'updateProfile']);

    // Subscription Plans Management (Admin Only)
    Route::get('/subscription-plans', [\App\Http\Controllers\Api\Admin\SubscriptionPlanController::class, 'index']);
    Route::post('/subscription-plans', [\App\Http\Controllers\Api\Admin\SubscriptionPlanController::class, 'store']);
    Route::get('/subscription-plans/{id}', [\App\Http\Controllers\Api\Admin\SubscriptionPlanController::class, 'show']);
    Route::put('/subscription-plans/{id}', [\App\Http\Controllers\Api\Admin\SubscriptionPlanController::class, 'update']);
    Route::delete('/subscription-plans/{id}', [\App\Http\Controllers\Api\Admin\SubscriptionPlanController::class, 'destroy']);
    Route::post('/subscription-plans/{id}/toggle-status', [\App\Http\Controllers\Api\Admin\SubscriptionPlanController::class, 'toggleStatus']);

    Route::post('/inquiries/{id}/assign', [\App\Http\Controllers\Api\Admin\InquiryController::class, 'assignLead']);

    // CMS Management

    // faqs
    Route::post('/faqs/update-status/{id}', [FAQController::class, 'updateStatus']);
    Route::apiResource('/faqs', FAQController::class);

    // Blog moderation
    Route::get('/blogs/pending', [BlogController::class, 'pending']);
    Route::get('/blogs/statistics', [BlogController::class, 'statistics']);
    Route::post('/blogs/{id}/approve', [BlogController::class, 'approve']);
    Route::post('/blogs/{id}/reject', [BlogController::class, 'reject']);
    
    // Blog categories
    Route::get('/blog-categories', [BlogController::class, 'indexCategories']);
    Route::post('/blog-categories', [BlogController::class, 'storeCategory']);
    Route::put('/blog-categories/{id}', [BlogController::class, 'updateCategory']);
    Route::post('/blog-categories/update-status/{id}', [BlogController::class, 'updateCategoryStatus']);
    Route::delete('/blog-categories/{id}', [BlogController::class, 'destroyCategory']);

    // blogs
    Route::post('/blogs/update-status/{id}', [BlogController::class, 'updateStatus']);
    Route::apiResource('/blogs', BlogController::class);

    // news
    Route::post('/news/update-status/{id}', [NewsController::class, 'updateStatus']);
    Route::apiResource('/news', NewsController::class);

    // Credit Packages Management
    Route::get('/credits', [AdminCreditController::class, 'index']);
    Route::get('/credits/{id}', [AdminCreditController::class, 'show']);
    Route::post('/credits', [AdminCreditController::class, 'store']);
    Route::put('/credits/{id}', [AdminCreditController::class, 'update']);
    Route::delete('/credits/{id}', [AdminCreditController::class, 'destroy']);

    // App Settings Management
    Route::get('/settings', [AppSettingController::class, 'index']);
    Route::get('/settings/{id}', [AppSettingController::class, 'show']);
    Route::post('/settings', [AppSettingController::class, 'store']);
    Route::put('/settings/{id}', [AppSettingController::class, 'update']);
    Route::post('/settings/bulk-update', [AppSettingController::class, 'bulkUpdate']);
    Route::delete('/settings/{id}', [AppSettingController::class, 'destroy']);

    // Wallet Management
    Route::get('/wallets', [AdminWalletController::class, 'index']);
    Route::get('/wallets/{userId}', [AdminWalletController::class, 'show']);
    Route::post('/wallets/{userId}/add-credits', [AdminWalletController::class, 'addCredits']);
    Route::post('/wallets/{userId}/deduct-credits', [AdminWalletController::class, 'deductCredits']);

    // Reports
    Route::get('/reports/credit-usage', [AdminWalletController::class, 'creditUsageReport']);
    Route::get('/transactions', [AdminWalletController::class, 'transactions']);

    // Blog Comments Dashboard
    Route::get('/blog-comments', [\App\Http\Controllers\Api\Admin\BlogCommentController::class, 'index']);
    Route::get('/blog-comments/statistics', [\App\Http\Controllers\Api\Admin\BlogCommentController::class, 'statistics']);
    Route::get('/blog-comments/{id}', [\App\Http\Controllers\Api\Admin\BlogCommentController::class, 'show']);
    Route::delete('/blog-comments/{id}', [\App\Http\Controllers\Api\Admin\BlogCommentController::class, 'destroy']);
});

// Agent routes
Route::middleware(['auth:sanctum', 'agent'])->prefix('agent')->group(function () {
    // Messages
    Route::get('/messages/unread-counts', [MessageController::class, 'getUnreadMessageCounts']);
    Route::get('/messages/customers', [MessageController::class, 'getAgentCustomers']);
    Route::post('/messages/is_typing', [MessageController::class, 'isTyping']);
    Route::post('/messages/sent', [MessageController::class, 'store']);
    Route::get('/messages/{userId}', [MessageController::class,'getConversation']);
    Route::post('/messages/read/{partnerId}', [MessageController::class, 'markAsRead']);

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

    // Agent Blog Management
    Route::get('/blogs', [\App\Http\Controllers\Api\Agent\BlogController::class, 'index']);
    Route::post('/blogs', [\App\Http\Controllers\Api\Agent\BlogController::class, 'store']);
    Route::get('/blogs/statistics', [\App\Http\Controllers\Api\Agent\BlogController::class, 'statistics']);
    Route::get('/blogs/categories', [\App\Http\Controllers\Api\Agent\BlogController::class, 'categories']);
    Route::get('/blogs/{id}', [\App\Http\Controllers\Api\Agent\BlogController::class, 'show']);
    Route::get('/blogs/{id}/comments', [\App\Http\Controllers\Api\Agent\BlogController::class, 'comments']);
    Route::put('/blogs/{id}', [\App\Http\Controllers\Api\Agent\BlogController::class, 'update']);
    Route::post('/blogs/{id}', [\App\Http\Controllers\Api\Agent\BlogController::class, 'update']); // For form-data
    Route::delete('/blogs/{id}', [\App\Http\Controllers\Api\Agent\BlogController::class, 'destroy']);
});

// Customer routes
Route::middleware(['auth:sanctum', 'customer'])->prefix('customer')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Api\Customer\DashboardController::class, 'index']);
    
    // Messages
    Route::get('/messages/unread-counts', [MessageController::class, 'getUnreadMessageCounts']);
    Route::post('/messages/is_typing', [MessageController::class, 'isTyping']);
    Route::post('/messages/sent', [MessageController::class, 'store']);
    Route::post('/messages/read/{partnerId}', [MessageController::class, 'markAsRead']);
    Route::get('/messages/agents', [MessageController::class, 'getCustomerAgents']);
    Route::get('/messages/{userId}', [MessageController::class, 'getConversation']);
    
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

    // Property Reviews
    Route::post('/properties/{propertyId}/reviews', [PropertyReviewController::class, 'store']);

    // Agent Reviews
    Route::post('/agent/{agentId}/reviews', [AgentReviewController::class, 'store']);

    // Wallet
    Route::get('/wallet', [CustomerWalletController::class, 'index']);
    Route::get('/wallet/balance', [CustomerWalletController::class, 'balance']);
    Route::get('/wallet/packages', [CustomerWalletController::class, 'packages']);
    Route::post('/wallet/buy', [CustomerWalletController::class, 'buy']);
    Route::post('/wallet/spend', [CustomerWalletController::class, 'spend']);
    Route::get('/wallet/transactions', [CustomerWalletController::class, 'transactions']);

    // Blog Comments
    Route::post('/blogs/{blogId}/comments', [\App\Http\Controllers\Api\Customer\BlogCommentController::class, 'store']);
    Route::get('/my-comments', [\App\Http\Controllers\Api\Customer\BlogCommentController::class, 'myComments']);
    Route::put('/comments/{id}', [\App\Http\Controllers\Api\Customer\BlogCommentController::class, 'update']);
    Route::delete('/comments/{id}', [\App\Http\Controllers\Api\Customer\BlogCommentController::class, 'destroy']);
});

// Rating public APIs
Route::get('/faqs', [FAQController::class, 'index']);
Route::get('/properties/{propertyId}/reviews', [PropertyReviewController::class, 'index']);
Route::get('/agent/{agentId}/reviews', [AgentReviewController::class, 'index']);

// Public property routes - Use FULL namespace
Route::get('/properties', [\App\Http\Controllers\Api\PropertyController::class, 'index']);
Route::get('/properties/search', [\App\Http\Controllers\Api\PropertyController::class, 'search']);
Route::get('/properties/{id}', [\App\Http\Controllers\Api\PropertyController::class, 'show'])->where('id', '[0-9]+');
Route::get('/properties/attributes', [\App\Http\Controllers\Api\AmenitiesController::class, 'index']);

// ========== PUBLIC BLOG ROUTES (ADD THIS SECTION) ==========
Route::prefix('blogs')->group(function () {
    Route::get('/featured', [\App\Http\Controllers\Api\BlogController::class, 'featured']);
    Route::get('/latest', [\App\Http\Controllers\Api\BlogController::class, 'latest']);
    Route::get('/popular', [\App\Http\Controllers\Api\BlogController::class, 'popular']);
    Route::get('/search', [\App\Http\Controllers\Api\BlogController::class, 'search']); // Before /{slug}
    Route::get('/statistics', [\App\Http\Controllers\Api\BlogController::class, 'statistics']);
    Route::get('/categories', [\App\Http\Controllers\Api\BlogController::class, 'categories']);
    Route::get('/category/{categorySlug}', [\App\Http\Controllers\Api\BlogController::class, 'byCategory']);
    Route::get('/author/{userId}', [\App\Http\Controllers\Api\BlogController::class, 'byAuthor']);
 
    Route::get('/', [\App\Http\Controllers\Api\BlogController::class, 'index']);
    Route::get('/{slug}', [\App\Http\Controllers\Api\BlogController::class, 'show']); // This should be LAST
    Route::get('/{slug}/related', [\App\Http\Controllers\Api\BlogController::class, 'related']);
    Route::get('/{slug}/comments', [\App\Http\Controllers\Api\BlogController::class, 'comments']);
});

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