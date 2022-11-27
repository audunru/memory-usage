<?php

namespace audunru\MemoryUsage\Tests\Feature;

use audunru\MemoryUsage\Helpers\MemoryHelper;
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
            'memory-usage.enabled' => true,
            'memory-usage.paths'   => [
                [
                    'patterns' => ['include*'],
                    'limit'    => 10,
                    'channel'  => null,
                    'level'    => 'warning',
                ],
                [
                    'patterns' => ['include*'],
                    'limit'    => 100,
                    'channel'  => 'slack',
                    'level'    => 'emergency',
                ],
                [
                    'patterns' => ['header*'],
                    'limit'    => 10,
                    'channel'  => null,
                    'level'    => 'warning',
                    'header'   => [
                        'environments' => ['testing'],
                    ],
                ],
            ],
            'memory-usage.ignore_patterns' => [
                'ignore*',
            ],
        ]);
    }

    public function testItLogsWarningToDefaultChannel()
    {
        $this->mock(MemoryHelper::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPeakUsage')
                ->andReturn(11);
        });

        $mockLogger = $this->mock(Logger::class, function (MockInterface $mock) {
            $mock->shouldReceive('log')
                ->once()
                ->with('warning', 'Maximum memory 11.00 MiB used during request for /include/test is greater than limit of 10.00 MiB');
            $mock->shouldReceive('log')
                ->once()
                ->with('error')
                ->never();
        });

        Log::shouldReceive('channel')
            ->once()
            ->with(null)
            ->andReturn($mockLogger);

        Log::shouldReceive('channel')
            ->with('slack')
            ->never();

        $this->get('/include/test');
    }

    public function testItLogsEmergencyToSlackChannel()
    {
        $this->mock(MemoryHelper::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPeakUsage')
                ->andReturn(101);
        });

        $mockLogger = $this->mock(Logger::class, function (MockInterface $mock) {
            $mock->shouldReceive('log')
                ->once()
                ->with('warning', 'Maximum memory 101.00 MiB used during request for /include/test is greater than limit of 10.00 MiB');
            $mock->shouldReceive('log')
                ->once()
                ->with('emergency', 'Maximum memory 101.00 MiB used during request for /include/test is greater than limit of 100.00 MiB');
        });

        Log::shouldReceive('channel')
            ->once()
            ->with(null)
            ->andReturn($mockLogger);

        Log::shouldReceive('channel')
            ->once()
            ->with('slack')
            ->andReturn($mockLogger);

        $this->get('/include/test');
    }

    public function testItExcludesPath()
    {
        Log::shouldReceive('channel')->never();

        $this->get('/exclude');
    }

    public function testItIgnoresPath()
    {
        Log::shouldReceive('channel')->never();

        $this->get('/ignore');
    }

    public function testItAddsHeaderToResponse()
    {
        $this->mock(MemoryHelper::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPeakUsage')
                ->andReturn(1);
        });

        $response = $this->get('/header');

        $response->assertHeader('memory-usage', '1');
    }

    public function testItDoesNotAddHeaderToResponse()
    {
        $response = $this->get('/no-header');

        $response->assertHeaderMissing('memory-usage');
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
