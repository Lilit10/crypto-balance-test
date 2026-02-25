<?php

namespace App\Providers;

use App\Repositories\BalanceOperationRepository;
use App\Repositories\BalanceOperationRepositoryInterface;
use App\Repositories\CryptoBalanceRepository;
use App\Repositories\CryptoBalanceRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CryptoBalanceRepositoryInterface::class, CryptoBalanceRepository::class);
        $this->app->bind(BalanceOperationRepositoryInterface::class, BalanceOperationRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
