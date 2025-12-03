<?php

/**
 * Admin Routes
 *
 * All routes in this file are prefixed with /admin and require authentication.
 * They should also have appropriate role/permission middleware applied.
 */


use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\InvestmentOpportunityController;
use App\Http\Controllers\Admin\InvestmentOpportunityController as AdminInvestmentOpportunityController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\BankTransferController;
use App\Http\Controllers\UserDeletionRequestController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\InvestmentCategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AppVersionController;

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {

    // ============================================
    // Dashboard Route
    // ============================================
    // Main admin dashboard - first page after login
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // New dashboard2 - focused on Opportunities, Investments, and Financials
    Route::get('/dashboard2', [DashboardController::class, 'dashboard2'])
        ->name('dashboard2');

    // Investment Performance API endpoint for chart filtering
    Route::get('/dashboard/investment-performance', [DashboardController::class, 'getInvestmentPerformance'])
        ->name('dashboard.investment-performance');

    // ============================================
    // User Management Routes
    // ============================================
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::resource('users', UserController::class)->except(['index']);

    // User - Admin Actions
    Route::prefix('users')->name('users.')->group(function () {
        // Toggle user status (activate/deactivate)
        Route::post('{user}/toggle-status', [UserController::class, 'toggleStatus'])
            ->name('toggle-status');

        // Manual verification
        Route::post('{user}/verify-email', [UserController::class, 'verifyEmail'])
            ->name('verify-email');
        Route::post('{user}/verify-phone', [UserController::class, 'verifyPhone'])
            ->name('verify-phone');

        // Wallet operations - Form display
        Route::get('{user}/deposit-form', [UserController::class, 'showDepositForm'])
            ->name('deposit-form');
        Route::get('{user}/withdraw-form', [UserController::class, 'showWithdrawForm'])
            ->name('withdraw-form');

        // Wallet operations - Processing
        Route::post('{user}/deposit', [UserController::class, 'deposit'])
            ->name('deposit');
        Route::post('{user}/withdraw', [UserController::class, 'withdraw'])
            ->name('withdraw');
    });

    // ============================================
    // Investment Opportunities Routes
    // ============================================
    Route::get('/investment-opportunities', [InvestmentOpportunityController::class, 'index'])
        ->name('investment-opportunities.index');
    Route::resource('investment-opportunities', InvestmentOpportunityController::class)
        ->except(['index']);

    // Investment Opportunities - Admin Actions
    Route::prefix('investment-opportunities')->name('investment-opportunities.')->group(function () {
        // Process merchandise delivery
        Route::post('{opportunity}/process-merchandise-delivery', [AdminInvestmentOpportunityController::class, 'processMerchandiseDelivery'])
            ->name('process-merchandise-delivery');

        // Record actual profit (modal)
        Route::get('{opportunity}/record-actual-profit', [AdminInvestmentOpportunityController::class, 'showRecordActualProfit'])
            ->name('record-actual-profit');
        Route::post('{opportunity}/record-actual-profit', [AdminInvestmentOpportunityController::class, 'recordActualProfit'])
            ->name('record-actual-profit.store');

        // Distribute returns
        Route::post('{opportunity}/distribute-returns', [AdminInvestmentOpportunityController::class, 'distributeReturns'])
            ->name('distribute-returns');

        // View merchandise status (modal)
        Route::get('{opportunity}/merchandise-status', [AdminInvestmentOpportunityController::class, 'showMerchandiseStatus'])
            ->name('merchandise-status');

        // View returns status (modal)
        Route::get('{opportunity}/returns-status', [AdminInvestmentOpportunityController::class, 'showReturnsStatus'])
            ->name('returns-status');
    });

    // ============================================
    // Investment Routes
    // ============================================
    // Note: Resource routes must come BEFORE custom routes with optional parameters
    // to avoid conflicts between show() and index(opportunity_id)
    Route::resource('investments', InvestmentController::class);

    // Alternative filtered index route for backward compatibility
    // Use query parameter instead: /admin/investments?opportunity_id=123
    // Or access via: /admin/investments/opportunity/{opportunity_id}
    Route::get('investments/opportunity/{opportunity_id}', [InvestmentController::class, 'index'])
        ->name('investments.by-opportunity');

    // Investment - Admin Actions
    Route::prefix('investments')->name('investments.')->group(function () {
        // Mark merchandise as arrived (for Myself type investments)
        Route::post('{investment}/mark-merchandise-arrived', [InvestmentController::class, 'markMerchandiseArrived'])
            ->name('mark-merchandise-arrived');

        // Distribute profit (for Authorize type investments)
        Route::post('{investment}/distribute-profit', [InvestmentController::class, 'distributeProfit'])
            ->name('distribute-profit');
    });


    // ============================================
    // Transaction Routes
    // ============================================
    Route::get('/transactions', [TransactionController::class, 'index'])
        ->name('transactions.index');
    Route::resource('transactions', TransactionController::class)
        ->except(['index']);

    // Alternative filtered index route for user transactions
    // Use query parameter instead: /admin/transactions?user_id=123
    // Or access via: /admin/transactions/user/{user_id}
    Route::get('transactions/user/{user_id}', [TransactionController::class, 'index'])
        ->name('transactions.by-user');

    // Transaction - Admin Actions
    Route::prefix('transactions')->name('transactions.')->group(function () {
        // Confirm pending transaction
        Route::post('{transaction}/confirm', [TransactionController::class, 'confirm'])
            ->name('confirm');

        // Export transaction details
        Route::get('{transaction}/export', [TransactionController::class, 'export'])
            ->name('export');
    });

    // ============================================
    // Withdrawal Routes
    // ============================================
    Route::get('/withdrawals', [WithdrawalController::class, 'index'])
        ->name('withdrawals.index');
    Route::resource('withdrawals', WithdrawalController::class)
        ->except(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    // Withdrawal - Admin Actions
    Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
        // Show rejection form
        Route::get('{withdrawal}/reject-form', [WithdrawalController::class, 'showRejectForm'])
            ->name('reject-form');

        // Update withdrawal status (approve/reject/process/complete)
        Route::post('{withdrawal}/status', [WithdrawalController::class, 'updateStatus'])
            ->name('update-status');
    });

    // ============================================
    // Bank Transfer Routes
    // ============================================
    Route::get('/bank-transfers', [BankTransferController::class, 'index'])
        ->name('bank-transfers.index');
    Route::resource('bank-transfers', BankTransferController::class)
        ->except(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    // Bank Transfer - Admin Actions
    Route::prefix('bank-transfers')->name('bank-transfers.')->group(function () {
        // Show approval form
        Route::get('{bankTransfer}/approve-form', [BankTransferController::class, 'showApproveForm'])
            ->name('approve-form');

        // Show rejection form
        Route::get('{bankTransfer}/reject-form', [BankTransferController::class, 'showRejectForm'])
            ->name('reject-form');

        // Update bank transfer status (approve/reject)
        Route::post('{bankTransfer}/status', [BankTransferController::class, 'updateStatus'])
            ->name('update-status');
    });

    // ============================================
    // Modal Content Routes
    // ============================================
    Route::get('/modal/investment-widgets', function(Request $request) {
        $opportunityId = $request->input('opportunity_id');
        $opportunity = $opportunityId ? \App\Models\InvestmentOpportunity::find($opportunityId) : null;

        return view('modals.investment-widgets', compact('opportunity'));
    })->name('modal.investment-widgets');

    Route::get('/modal/mixed-widget-demo', function(Request $request) {
        $opportunityId = $request->input('opportunity_id');
        $opportunity = $opportunityId ? \App\Models\InvestmentOpportunity::find($opportunityId) : null;

        return view('modals.mixed-widget-demo', compact('opportunity'));
    })->name('modal.mixed-widget-demo');



    // ============================================
    // Content Routes
    // ============================================
    Route::get('/contents', [ContentController::class, 'index'])
        ->name('contents.index');
    Route::resource('contents', ContentController::class)
        ->except(['index']);


    // ============================================
    // FAQ Routes
    // ============================================
    Route::get('/faqs', [FAQController::class, 'index'])
        ->name('faqs.index');
    Route::resource('faqs', FAQController::class)
        ->except(['index']);

    // ============================================
    // User Deletion Request Routes
    // ============================================
    Route::get('/user-deletion-requests', [UserDeletionRequestController::class, 'index'])
        ->name('user-deletion-requests.index');
    Route::resource('user-deletion-requests', UserDeletionRequestController::class)
        ->except(['index', 'create', 'store']);

    // User Deletion Request - Admin Actions
    Route::prefix('user-deletion-requests')->name('user-deletion-requests.')->group(function () {
        // Approve deletion request
        Route::post('{userDeletionRequest}/approve', [UserDeletionRequestController::class, 'approve'])
            ->name('approve');

        // Show rejection form
        Route::get('{userDeletionRequest}/reject-form', [UserDeletionRequestController::class, 'showRejectForm'])
            ->name('reject-form');

        // Reject deletion request
        Route::post('{userDeletionRequest}/reject', [UserDeletionRequestController::class, 'reject'])
            ->name('reject');

        // Cancel deletion request
        Route::post('{userDeletionRequest}/cancel', [UserDeletionRequestController::class, 'cancel'])
            ->name('cancel');
    });

    // ============================================
    // Contact Message Routes
    // ============================================
    Route::get('/contact-messages', [ContactMessageController::class, 'index'])
        ->name('contact-messages.index');
    Route::resource('contact-messages', ContactMessageController::class)
        ->except(['index']);

    // ============================================
    // Bank Routes
    // ============================================
    Route::get('/banks', [BankController::class, 'index'])
        ->name('banks.index');
    Route::resource('banks', BankController::class)
        ->except(['index']);

    // ============================================
    // Notification Routes (Admin)
    // ============================================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        // Send notification to specific users
        Route::post('send-to-users', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'sendToUsers'])
            ->name('send-to-users');

        // Send notification to all users
        Route::post('send-to-all', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'sendToAllUsers'])
            ->name('send-to-all');

        // Send notification to investors only
        Route::post('send-to-investors', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'sendToInvestors'])
            ->name('send-to-investors');

        // Send notification to owners only
        Route::post('send-to-owners', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'sendToOwners'])
            ->name('send-to-owners');
    });

    // ============================================
    // Investment Category Routes
    // ============================================
    Route::get('/investment-categories', [InvestmentCategoryController::class, 'index'])
        ->name('investment-categories.index');
    Route::resource('investment-categories', InvestmentCategoryController::class)
        ->except(['index']);

    // ============================================
    // App Version Management Routes
    // ============================================
    Route::get('/app-versions', [AppVersionController::class, 'index'])
        ->name('app-versions.index');
    Route::resource('app-versions', AppVersionController::class)
        ->except(['index']);

    // App Version - Admin Actions
    Route::prefix('app-versions')->name('app-versions.')->group(function () {
        // Toggle active status
        Route::post('{appVersion}/toggle-status', [AppVersionController::class, 'toggleStatus'])
            ->name('toggle-status');
    });

});
