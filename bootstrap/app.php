<?php

use App\Exceptions\GlobalApiExceptionHandler;
use App\Http\Middleware\InjectCurrentProfile;
use App\Http\Middleware\OptionalAuth;
use App\Http\Middleware\CheckAppVersion;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(append: [
            InjectCurrentProfile::class,
            CheckAppVersion::class,
        ]);

        $middleware->alias([
            'optional.auth' => OptionalAuth::class,
            'check.app.version' => CheckAppVersion::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Global API exception handler using ApiResponseTrait structure
        $exceptions->render(function (Exception $e, $request) {
            return GlobalApiExceptionHandler::handleApiException($e, $request);
        });
    })->create();
