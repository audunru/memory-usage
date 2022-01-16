<?php

namespace audunru\MemoryUsage\Tests;

use audunru\MemoryUsage\MemoryUsageServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @SuppressWarnings("unused")
     */
    protected function getPackageProviders($app)
    {
        return [MemoryUsageServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.debug', 'true' === env('APP_DEBUG'));
        $app['config']->set('app.key', substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyz', 5)), 0, 32));
        $app->register(MemoryUsageServiceProvider::class);
    }

    protected function defineRoutes($router)
    {
        $router->get('/ignore', function () {
            return [];
        });
    }
}
