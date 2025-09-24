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
Route::get('/investor/home', [\App\Http\Controllers\Api\InvestmentOpportunityController::class, 'home'])->middleware('optional.auth');
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

        // POST /user/auth/register
        Route::post('register', 'register')->name('user.auth.register');

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

//});
