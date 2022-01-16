<?php

namespace audunru\MemoryUsage\Tests\Feature;

use audunru\MemoryUsage\Contracts\MemoryHelperContract;
use audunru\MemoryUsage\Tests\TestCase;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;

class MemoryUsageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'memory-usage.enabled'    => true,
            'memory-usage.paths'      => [
                [
                    'patterns' => ['include*'],
                    'limit'    => 10,
                    'channel'  => 'stack',
                    'level'    => 'warning',
                ],
                [
                    'patterns' => ['include*'],
                    'limit'    => 100,
                    'channel'  => 'slack',
                    'level'    => 'emergency',
                ],
            ],
            'memory-usage.ignore_patterns' => [
                'ignore*',
            ],
        ]);
    }

    public function testItLogsWarningToStackChannel()
    {
        $this->mock(MemoryHelperContract::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPeakUsage')
                ->andReturn(11);
        });

        $mockLogger = $this->mock(Logger::class, function (MockInterface $mock) {
            $mock->shouldReceive('log')
                ->with('warning', 'Maximum memory 11.00 MiB used during request for /include/test is greater than limit of 10.00 MiB');
            $mock->shouldReceive('log')
                ->with('error')
                ->never();
        });

        Log::shouldReceive('channel')
            ->with('stack')
            ->once()
            ->andReturn($mockLogger);

        Log::shouldReceive('channel')
            ->with('slack')
            ->never();

        $this->get('/include/test');
    }

    public function testItLogsEmergencyToSlackChannel()
    {
        $this->mock(MemoryHelperContract::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPeakUsage')
                ->andReturn(101);
        });

        $mockLogger = $this->mock(Logger::class, function (MockInterface $mock) {
            $mock->shouldReceive('log')
                ->with('warning', 'Maximum memory 101.00 MiB used during request for /include/test is greater than limit of 10.00 MiB');
            $mock->shouldReceive('log')
                ->with('emergency', 'Maximum memory 101.00 MiB used during request for /include/test is greater than limit of 100.00 MiB');
        });

        Log::shouldReceive('channel')
            ->with('stack')
            ->once()
            ->andReturn($mockLogger);

        Log::shouldReceive('channel')
            ->with('slack')
            ->once()
            ->andReturn($mockLogger);

        $this->get('/include/test');
    }

    public function testItExcludesPath()
    {
        $this->mock(MemoryHelperContract::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPeakUsage')
                ->andReturn(1000);
        });

        Log::shouldReceive('channel')->never();

        $this->get('/exclude');
    }

    public function testItIgnoresPath()
    {
        Log::shouldReceive('channel')->never();

        $this->get('/ignore');
    }

    // TODO: Service provider has already loaded and enabled the listener
    // public function testItCanBeDisabled()
    // {
    //     config([
    //         'memory-usage.enabled'    => false,
    //     ]);

    //     Log::shouldReceive('channel')
    //         ->never();

    //     $this->get('/test');
    // }
}
