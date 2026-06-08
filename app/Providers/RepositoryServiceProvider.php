<?php

namespace App\Providers;

use App\Repositories\Contracts\DeviceBindingRepositoryInterface;
use App\Repositories\Eloquent\DeviceBindingRepository;
use App\Repositories\Contracts\DeviceLogRepositoryInterface;
use App\Repositories\Eloquent\DeviceLogRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(DeviceBindingRepositoryInterface::class, DeviceBindingRepository::class);
        $this->app->bind(DeviceLogRepositoryInterface::class, DeviceLogRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
