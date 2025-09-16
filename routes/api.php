<?php

use App\Http\Controllers\Api\UserAuthController;
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
Route::get('/investor/home', [\App\Http\Controllers\Api\InvestmentOpportunityController::class, 'home']);
Route::post('/investor/invest', [\App\Http\Controllers\Api\InvestmentOpportunityController::class, 'invest'])->middleware('auth:sanctum');
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


//});
