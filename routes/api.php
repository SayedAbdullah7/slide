<?php

use App\Http\Controllers\Api\UserAuthController;
use App\Http\Controllers\Api\ContentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Route::post('/auth/send-otp', [\App\Http\Controllers\Api\UserAuthController::class, 'sendOtp']);
//Route::post('/auth/verify-otp', [\App\Http\Controllers\Api\UserAuthController::class, 'verifyOtp']);
//Route::post('/auth/register', [\App\Http\Controllers\Api\UserAuthController::class, 'register']);
//Route::post('/auth/question', [\App\Http\Controllers\Api\UserAuthController::class, 'question']);
// Grouping all auth-related routes under a common prefix and namespace
//Route::prefix('user')->group(function () {

Route::get('/investment-opportunities', [\App\Http\Controllers\Api\InvestmentOpportunityController::class, 'index']);
Route::get('/investor/home', [\App\Http\Controllers\Api\InvestmentOpportunityController::class, 'home'])->middleware('auth:sanctum');
Route::post('/investor/invest', [\App\Http\Controllers\Api\InvestmentOpportunityController::class, 'invest'])->middleware('auth:sanctum');

// Investment management routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/investor/my-investments', [\App\Http\Controllers\Api\InvestmentOpportunityController::class, 'myInvestments']);
    Route::get('/investor/my-investments/{opportunityId}', [\App\Http\Controllers\Api\InvestmentOpportunityController::class, 'myInvestmentDetails']);
    Route::get('/investor/investment-stats', [\App\Http\Controllers\Api\InvestmentOpportunityController::class, 'investmentStats']);

    // New investment management routes
    Route::prefix('investor')->group(function () {
        Route::get('/investments', [\App\Http\Controllers\InvestorInvestmentController::class, 'index']);
        Route::get('/investments/{investment}', [\App\Http\Controllers\InvestorInvestmentController::class, 'show']);
        Route::get('/investments-statistics', [\App\Http\Controllers\InvestorInvestmentController::class, 'statistics']);
        Route::get('/distribution-history', [\App\Http\Controllers\InvestorInvestmentController::class, 'distributionHistory']);
        Route::get('/investments/status/{status}', [\App\Http\Controllers\InvestorInvestmentController::class, 'getByStatus']);
    });
});

// Admin investment management routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::prefix('investments')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminInvestmentController::class, 'dashboard']);
        Route::get('/opportunity/{opportunity}/management', [\App\Http\Controllers\Admin\AdminInvestmentController::class, 'opportunityManagement']);
        Route::post('/opportunity/{opportunity}/merchandise-delivery', [\App\Http\Controllers\Admin\AdminInvestmentController::class, 'processMerchandiseDelivery']);
        Route::post('/opportunity/{opportunity}/actual-returns', [\App\Http\Controllers\Admin\AdminInvestmentController::class, 'processActualReturns']);
        Route::post('/opportunity/{opportunity}/distribution', [\App\Http\Controllers\Admin\AdminInvestmentController::class, 'processReturnsDistribution']);
        Route::get('/requiring-attention', [\App\Http\Controllers\Admin\AdminInvestmentController::class, 'getInvestmentsRequiringAttention']);
        Route::get('/opportunity/{opportunity}/lifecycle-status', [\App\Http\Controllers\Admin\AdminInvestmentController::class, 'getInvestmentLifecycleStatus']);
        Route::get('/investor-performance', [\App\Http\Controllers\Admin\AdminInvestmentController::class, 'getInvestorPerformance']);
        Route::get('/opportunity-performance', [\App\Http\Controllers\Admin\AdminInvestmentController::class, 'getOpportunityPerformance']);
        Route::get('/financial-summary', [\App\Http\Controllers\Admin\AdminInvestmentController::class, 'getFinancialSummary']);
        Route::post('/bulk-update-statuses', [\App\Http\Controllers\Admin\AdminInvestmentController::class, 'bulkUpdateStatuses']);
    });
});
// Group all auth routes under /user/auth
Route::prefix('auth')->controller(UserAuthController::class)->group(function () {

    Route::get('check-phone', 'checkPhone')->name('user.auth.checkPhone');

    // POST /user/auth/send-otp
    Route::post('send-otp', 'sendOtp')->name('user.auth.sendOtp');

    // POST /user/auth/verify-otp
    Route::post('verify-otp', 'verifyOtp')->name('user.auth.verifyOtp');

    // POST /user/auth/register (supports both authenticated and non-authenticated)
    Route::post('register', 'register')->middleware('optional.auth')->name('user.auth.register');

    // POST /user/auth/question
});
Route::get('auth/questions', [\App\Http\Controllers\SurveyQuestionController::class, 'index'])->name('user.auth.question');

Route::middleware('auth:sanctum')->put('auth/set-password', [UserAuthController::class, 'setPassword'])
    ->name('user.auth.setPassword');

// PATCH /auth/switch-profile
Route::middleware('auth:sanctum')->patch('auth/switch-profile', [UserAuthController::class, 'switchProfile'])
    ->name('user.auth.switchProfile');

// POST /auth/logout
Route::middleware('auth:sanctum')->post('auth/logout', [UserAuthController::class, 'logout'])
    ->name('user.auth.logout');

// POST /auth/request-deletion
Route::middleware('auth:sanctum')->delete('auth/request-deletion', [UserAuthController::class, 'requestDeletion']);


// Wallet API Routes
Route::middleware(['auth:sanctum'])->prefix('wallet')->controller(\App\Http\Controllers\Api\WalletController::class)->group(function () {

    // Get wallet screen (main wallet page)
    Route::get('/', 'index')->name('api.wallet.index');

    // Get quick actions
    Route::get('quick-actions', 'getQuickActions')->name('api.wallet.quick-actions');

    // Toggle balance visibility
    Route::post('toggle-visibility', 'toggleBalanceVisibility')->name('api.wallet.toggle-visibility');

    // Get wallet balance
    Route::get('balance', 'getBalance')->name('api.wallet.balance');

    // Deposit money to wallet
    Route::post('deposit', 'deposit')->name('api.wallet.deposit');

    // Withdraw money from wallet
    Route::post('withdraw', 'withdraw')->name('api.wallet.withdraw');

    // Transfer money to another profile
    Route::post('transfer', 'transfer')->name('api.wallet.transfer');

    // Get transaction history
    Route::get('transactions', 'getTransactions')->name('api.wallet.transactions');

    // Create wallet (if needed)
    Route::post('create', 'createWallet')->name('api.wallet.create');
});

// Withdrawal API Routes
Route::middleware(['auth:sanctum'])->prefix('withdrawal')->controller(\App\Http\Controllers\Api\WithdrawalController::class)->group(function () {

    // Get available balance for withdrawal
    Route::get('available-balance', 'getAvailableBalance')->name('api.withdrawal.available-balance');

    // Get list of Saudi banks
    Route::get('banks', 'getBanks')->name('api.withdrawal.banks');

    // Get saved bank accounts
    Route::get('bank-accounts', 'getBankAccounts')->name('api.withdrawal.bank-accounts');

    // Add new bank account
    Route::post('bank-accounts', 'addBankAccount')->name('api.withdrawal.bank-accounts.store');

    // Delete bank account
    Route::delete('bank-accounts/{bankAccountId}', 'deleteBankAccount')->name('api.withdrawal.bank-accounts.delete');

    // Create withdrawal request
    Route::post('request', 'createWithdrawalRequest')->name('api.withdrawal.request');

    // Get withdrawal history
    Route::get('history', 'getWithdrawalHistory')->name('api.withdrawal.history');
});

// Bank Transfer routes (Deposit via bank transfer)

// Public route - Get company bank account details (no auth required)
Route::get('bank-transfer/company-account', [\App\Http\Controllers\Api\BankTransferController::class, 'getCompanyBankAccount'])
    ->name('api.bank-transfer.company-account');

Route::middleware(['auth:sanctum'])->prefix('bank-transfer')->controller(\App\Http\Controllers\Api\BankTransferController::class)->group(function () {

    // Submit bank transfer request with receipt
    Route::post('request', 'submitBankTransfer')->name('api.bank-transfer.request');

    // Get bank transfer request history
    Route::get('history', 'getBankTransferHistory')->name('api.bank-transfer.history');
});

Route::prefix('investor')->group(function () {
    // Investment Opportunity Reminder API Routes
    Route::middleware(['auth:sanctum'])->prefix('reminders')->controller(\App\Http\Controllers\Api\InvestmentOpportunityReminderController::class)->group(function () {

        // Get all reminders for the authenticated investor
        Route::get('/', 'index')->name('api.reminders.index');

        // Add a reminder for a coming investment opportunity
        Route::post('/', 'store')->name('api.reminders.store');

        // Get coming opportunities that can have reminders
        Route::get('coming-opportunities', 'comingOpportunities')->name('api.reminders.coming-opportunities');

        // Get reminder statistics
        Route::get('stats', 'stats')->name('api.reminders.stats');

        // Toggle reminder status (activate/deactivate)
        Route::patch('{reminderId}/toggle', 'toggle')->name('api.reminders.toggle');

        // Remove a reminder
        Route::delete('{reminderId}', 'destroy')->name('api.reminders.destroy');
    });

    // Saved Investment Opportunities API Routes
    Route::middleware(['auth:sanctum'])->prefix('saved-opportunities')->controller(\App\Http\Controllers\Api\SavedInvestmentOpportunityController::class)->group(function () {

        // Get all saved opportunities for the authenticated investor
        Route::get('/', 'index')->name('api.saved-opportunities.index');

        // Save an investment opportunity
        Route::post('/', 'store')->name('api.saved-opportunities.store');

        // Remove a saved investment opportunity
        Route::delete('/', 'destroy')->name('api.saved-opportunities.destroy');

        // Toggle save status of an investment opportunity
        Route::post('toggle', 'toggle')->name('api.saved-opportunities.toggle');

        // Check save status for multiple opportunities
        Route::post('check-status', 'checkStatus')->name('api.saved-opportunities.check-status');

        // Get save statistics
        Route::get('stats', 'stats')->name('api.saved-opportunities.stats');
    });

    // Statistics Dashboard API Routes
    Route::middleware(['auth:sanctum'])->prefix('statistics')->controller(\App\Http\Controllers\Api\StatisticsController::class)->group(function () {

        // Get statistics dashboard data
        Route::get('/', 'getStatistics')->name('api.statistics.index');

        // Get statistics data for specific period
        Route::get('period/{period}', 'getStatisticsByPeriod')->name('api.statistics.period');

        // Get investment trends over time
        Route::get('trends', 'getInvestmentTrends')->name('api.statistics.trends');

        // Get quick statistics summary
        Route::get('summary', 'getQuickSummary')->name('api.statistics.summary');

        // Get statistics comparison between periods
        Route::get('comparison', 'getStatisticsComparison')->name('api.statistics.comparison');
    });
});

// Owner Investment Opportunity Request API Routes
Route::middleware(['auth:sanctum'])->prefix('owner')->group(function () {
    Route::prefix('opportunity-requests')->controller(\App\Http\Controllers\Api\OwnerOpportunityRequestController::class)->group(function () {
        // Get all opportunity requests for the authenticated owner
        Route::get('/', 'index')->name('api.owner.opportunity-requests.index');

        // Submit a new opportunity request
        Route::post('/', 'store')->name('api.owner.opportunity-requests.store');

        // Get a specific opportunity request
        Route::get('/{id}', 'show')->name('api.owner.opportunity-requests.show');

        // Update an opportunity request
        Route::put('/{id}', 'update')->name('api.owner.opportunity-requests.update');

        // Delete an opportunity request
        Route::delete('/{id}', 'destroy')->name('api.owner.opportunity-requests.destroy');

        // Get available guarantee types
        // Route::get('/guarantee-types', 'getGuaranteeTypes')->name('api.owner.opportunity-requests.guarantee-types');

        // Get available request statuses
        // move
        Route::get('/statuses', 'getStatuses')->name('api.owner.opportunity-requests.statuses');

        // Get request statistics
        Route::get('/statistics', 'getStatistics')->name('api.owner.opportunity-requests.statistics');

        // Dashboard API route

    });
    Route::middleware(['auth:sanctum'])->get('/home', [\App\Http\Controllers\Api\OwnerOpportunityRequestController::class, 'getDashboard'])->name('api.owner.dashboard');

    // Owner Investment Opportunity API Routes
    Route::middleware(['auth:sanctum'])->prefix('investment-opportunities')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\OwnerInvestmentOpportunityController::class, 'index'])->name('api.owner.investment-opportunities.index');
    });



    // Route::middleware(['auth:sanctum'])->prefix('owner')->group(function () {
    // Get guarantee-types
    Route::get('/guarantee-types', [\App\Http\Controllers\Api\OwnerOpportunityRequestController::class, 'getGuaranteeTypes'])->name('api.owner.opportunity-requests.guarantee-types');
    // });
});

// FCM Token Management API Routes
Route::middleware(['auth:sanctum'])->prefix('fcm')->controller(\App\Http\Controllers\Api\FcmTokenController::class)->group(function () {

    // Register FCM token
    Route::post('register', 'register')->name('api.fcm.register');

    // Get all FCM tokens for user
    Route::get('tokens', 'index')->name('api.fcm.tokens');

    // Update FCM token
    Route::put('tokens/{tokenId}', 'update')->name('api.fcm.update');

    // Remove FCM token
    Route::delete('tokens', 'remove')->name('api.fcm.remove');

    // Test notification
    Route::post('test', 'testNotification')->name('api.fcm.test');

    // Get notification statistics
    Route::get('stats', 'stats')->name('api.fcm.stats');

    // Deactivate all tokens
    Route::post('deactivate-all', 'deactivateAll')->name('api.fcm.deactivate-all');
});

// Notifications API Routes
Route::middleware(['auth:sanctum'])->prefix('notifications')->controller(\App\Http\Controllers\Api\NotificationController::class)->group(function () {

    // Get user's notifications
    Route::get('/', 'index')->name('api.notifications.index');

    // Get unread notifications count
    Route::get('unread-count', 'unreadCount')->name('api.notifications.unread-count');

    // Get notification statistics
    Route::get('stats', 'stats')->name('api.notifications.stats');

    // Get notification settings
    Route::get('settings', 'getSettings')->name('api.notifications.settings');

    // Update notification settings
    Route::post('settings', 'updateSettings')->name('api.notifications.update-settings');

    // Mark notification as read
    Route::post('{id}/read', 'markAsRead')->name('api.notifications.mark-as-read');

    // Mark all notifications as read
    Route::post('mark-all-read', 'markAllAsRead')->name('api.notifications.mark-all-read');

    // Delete notification
    Route::delete('delete/{id}', 'destroy')->name('api.notifications.destroy');

    // Delete all notifications
    Route::delete('delete_all', 'deleteAll')->name('api.notifications.delete-all');
});

// Content API Routes (Public - no authentication required)
Route::prefix('content')->controller(ContentController::class)->group(function () {

    // Get Privacy Policy
    Route::get('privacy-policy', 'privacyPolicy')->name('api.content.privacy-policy');

    // Get Terms and Conditions
    Route::get('terms-and-conditions', 'termsAndConditions')->name('api.content.terms-and-conditions');

    // Get About App content
    Route::get('about-app', 'aboutApp')->name('api.content.about-app');

    // Get FAQ list
    Route::get('faq', 'faq')->name('api.content.faq');

    // Get specific FAQ by ID
    Route::get('faq/{id}', 'faqDetails')->name('api.content.faq.details');

    // Get all content in one response
    Route::get('all', 'allContent')->name('api.content.all');
});

// Contact API Routes
Route::prefix('contact')->controller(\App\Http\Controllers\Api\ContactController::class)->group(function () {

    // Submit contact message (public - no authentication required)
    Route::post('/', 'store')->name('api.contact.store');

    // Get user's contact messages (requires authentication)
    Route::middleware('auth:sanctum')->get('/', 'index')->name('api.contact.index');

    // Get specific contact message details (requires authentication)
    Route::middleware('auth:sanctum')->get('{id}', 'show')->name('api.contact.show');
});

// Payment API Routes (Authenticated)
Route::prefix('payments')->middleware('auth:sanctum')->controller(\App\Http\Controllers\Api\PaymentController::class)->group(function () {

    // Create investment payment intention
    Route::post('intentions', 'createIntention')->name('api.payments.intentions.create');

    // Create wallet charging intention
    Route::post('wallet-intentions', 'createWalletIntention')->name('api.payments.wallet-intentions.create');

    // Create investment payment intention
    Route::post('investment-intentions', 'createInvestmentIntention')->name('api.payments.investment-intentions.create');

    // Get user's payment intentions list
    Route::get('intentions', 'getIntentions')->name('api.payments.intentions.index');

    // Get user's payment transactions list
    Route::get('transactions', 'getTransactions')->name('api.payments.transactions.index');

    // Get payment statistics
    Route::get('stats', 'getPaymentStats')->name('api.payments.stats');

    // Get payment logs
    Route::get('logs', 'getPaymentLogs')->name('api.payments.logs');
});

// User Saved Cards API Route (Authenticated)
Route::prefix('cards')->middleware('auth:sanctum')->controller(\App\Http\Controllers\Api\UserCardController::class)->group(function () {

    // Get list of user's saved cards
    Route::get('/', 'index')->name('api.cards.index');
});

// Paymob Webhooks (Public - no authentication)
Route::prefix('paymob')->controller(\App\Http\Controllers\Api\PaymentWebhookController::class)->group(function () {

    // Main webhook - handles both TRANSACTION and TOKEN types
    Route::post('webhook', 'handlePaymobWebhook')->name('api.paymob.webhook');

    // Notification webhook - TRANSACTION type (optional specific endpoint)
    Route::post('notification', 'notification')->name('api.paymob.notification');

    // Tokenized callback - TOKEN type (optional specific endpoint)
    Route::post('tokenized-callback', 'tokenizedCallback')->name('api.paymob.tokenized-callback');
});

//});
