<?php

/**
 * Web Routes
 *
 * These routes are loaded by the RouteServiceProvider and all of them will
 * be assigned to the "web" middleware group. Make something great!
 */

use App\Http\Controllers\Api\PaymentWebhookController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InvestmentOpportunityController;


Route::get('/simulate-paymob-webhook', function () {
    $payload = [
        "type" => "TOKEN",
        "obj" => [
            "id" => 27911,
            "token" => "5fe444640033d1c5696ac76f2360af7f2c38f6c72fd18c0f5c644ac0",
            "masked_pan" => "xxxx-xxxx-xxxx-0008",
            "merchant_id" => 11883,
            "card_subtype" => "MasterCard",
        "created_at" => "2025-10-14T22:46:57.977092+03:00",
            "email" => "sayed@gmail.com",
            "order_id" => "1037965",
            "user_added" => false,
            "next_payment_intention" => "pi_test_4c022580ecca4f1f9ae38f6d9778c835"
        ],
        "hmac" => "2c89c91fad5cb95b6f399536284155339b931e42998123ee59e967ebcb4e8f0f7f81aa93ffab06d372e4b67b05c04e29f965cd3be8ef94fbe77158daf4440eb3"
    ];

    // Create empty Request and merge payload
    $request = Request::create('/api/paymob/webhook', 'POST');
    $request->headers->set('Content-Type', 'application/json');
    $request->merge($payload);

    $r = app(PaymentWebhookController::class)->handlePaymobWebhook($request);
    echo $r;
});

// ============================================
// Public Routes
// ============================================
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ============================================
// Dashboard Route
// ============================================
// Dashboard route is in admin.php
// For authenticated users, redirect to admin dashboard
Route::get('dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ============================================
// Authenticated User Routes
// ============================================
Route::middleware(['auth'])->group(function () {

    // User Profile Management Routes
    Route::prefix('user')->name('user.')->group(function () {
        // Investor profile routes
        Route::get('/{user}/investor-profile/create', [UserController::class, 'createInvestorProfile'])->name('investor-profile.create');
        Route::post('/{user}/investor-profile', [UserController::class, 'storeInvestorProfile'])->name('investor-profile.store');
        Route::get('/{user}/investor-profile/edit', [UserController::class, 'editInvestorProfile'])->name('investor-profile.edit');
        Route::put('/{user}/investor-profile', [UserController::class, 'updateInvestorProfile'])->name('investor-profile.update');

        // Owner profile routes
        Route::get('/{user}/owner-profile/create', [UserController::class, 'createOwnerProfile'])->name('owner-profile.create');
        Route::post('/{user}/owner-profile', [UserController::class, 'storeOwnerProfile'])->name('owner-profile.store');
        Route::get('/{user}/owner-profile/edit', [UserController::class, 'editOwnerProfile'])->name('owner-profile.edit');
        Route::put('/{user}/owner-profile', [UserController::class, 'updateOwnerProfile'])->name('owner-profile.update');
    });

    // Settings Routes
    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
});

// ============================================
// Backward Compatibility Routes
// ============================================
Route::middleware(['auth'])->group(function () {
    // Alternative user routes (for backward compatibility)
    Route::resource('user', UserController::class);

    // Alternative investment opportunity routes (for backward compatibility)
    Route::resource('investment-opportunity', InvestmentOpportunityController::class);
});

// ============================================
// Include Additional Route Files
// ============================================

// Admin routes
require __DIR__.'/admin.php';

// Authentication routes
require __DIR__.'/auth.php';

// Test routes (only load in non-production environments)
if (app()->environment(['local', 'staging', 'development'])) {
    require __DIR__.'/test.php';
}
