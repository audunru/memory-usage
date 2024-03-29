<?php

namespace audunru\MemoryUsage;

use audunru\MemoryUsage\Listeners\LogMemoryUsage;
use audunru\MemoryUsage\Listeners\LogSlowResponse;
use Illuminate\Foundation\Http\Events\RequestHandled;
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
            Event::listen(RequestHandled::class, LogMemoryUsage::class);
        }
        if (config('memory-usage.slow_response_enabled')) {
            Event::listen(RequestHandled::class, LogSlowResponse::class);
        }
    }
}
