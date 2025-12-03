<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Contracts\WalletServiceInterface;
use App\Services\Contracts\InvestmentServiceInterface;
use App\Services\Contracts\StatisticsServiceInterface;
use App\Services\Contracts\InvestmentValidationServiceInterface;
use App\Services\Contracts\InvestmentCalculatorServiceInterface;
use App\Services\WalletService;
use App\Services\InvestmentService;
use App\Services\StatisticsService;
use App\Services\InvestmentValidationService;
use App\Services\InvestmentCalculatorService;

class ServiceBindingProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind service interfaces to their implementations
        $this->app->bind(WalletServiceInterface::class, WalletService::class);
        $this->app->bind(InvestmentServiceInterface::class, InvestmentService::class);
        $this->app->bind(StatisticsServiceInterface::class, StatisticsService::class);
        $this->app->bind(InvestmentValidationServiceInterface::class, InvestmentValidationService::class);
        $this->app->bind(InvestmentCalculatorServiceInterface::class, InvestmentCalculatorService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
