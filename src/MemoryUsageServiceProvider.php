<?php

namespace audunru\MemoryUsage;

use audunru\MemoryUsage\Http\Middleware\LogMemoryUsage;
use audunru\MemoryUsage\Http\Middleware\LogSlowResponse;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Event;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MemoryUsageServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('memory-usage')
            ->hasConfigFile();
    }

    /**
     * Register any package services.
     */
    public function packageBooted()
    {
        if (config('memory-usage.enabled')) {
            Event::listen(RouteMatched::class, fn () => memory_reset_peak_usage());
            $this->app->make(Kernel::class)->pushMiddleware(LogMemoryUsage::class);
        }
        if (config('memory-usage.slow_response_enabled')) {
            $this->app->make(Kernel::class)->pushMiddleware(LogSlowResponse::class);
        }
    }
}
