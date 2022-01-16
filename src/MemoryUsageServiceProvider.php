<?php

namespace audunru\MemoryUsage;

use audunru\MemoryUsage\Contracts\MemoryHelperContract;
use audunru\MemoryUsage\Listeners\LogMemoryUsage;
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
    public function packageRegistered()
    {
        $this->app->bind(
            MemoryHelperContract::class,
            config('memory-usage.memory-helper')
        );
    }

    /**
     * Register any package services.
     */
    public function packageBooted()
    {
        if (config('memory-usage.enabled')) {
            Event::listen(RequestHandled::class, LogMemoryUsage::class);
        }
    }
}
