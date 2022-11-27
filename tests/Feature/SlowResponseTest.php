<?php

namespace audunru\MemoryUsage\Tests\Feature;

use audunru\MemoryUsage\Helpers\MemoryHelper;
use audunru\MemoryUsage\Helpers\TimeHelper;
use audunru\MemoryUsage\Tests\TestCase;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;

class SlowResponseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'memory-usage.paths' => [
                [
                    'patterns'            => ['include*'],
                    'limit'               => 10,
                    'slow_response_limit' => 3,
                    'channel'             => null,
                    'level'               => 'warning',
                ],
                [
                    'patterns'            => ['include*'],
                    'limit'               => 10,
                    'slow_response_limit' => 10,
                    'channel'             => 'slack',
                    'level'               => 'emergency',
                ],
            ],
            'memory-usage.ignore_patterns' => [
                'ignore*',
            ],
        ]);

        // Exclude memory usage logging when testing slow responses
        $this->mock(MemoryHelper::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPeakUsage')
                ->andReturn(0);
        });
    }

    public function testItLogsSlowResponseWarningToDefaultChannel()
    {
        $this->mock(TimeHelper::class, function (MockInterface $mock) {
            $mock->shouldReceive('getResponseTime')
                ->andReturn(5);
        });

        $mockLogger = $this->mock(Logger::class, function (MockInterface $mock) {
            $mock->shouldReceive('log')
                ->once()
                ->with('warning', 'Response time 5.00 s for /include/test is greater than limit of 3.00 s');
            $mock->shouldReceive('log')
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

    public function testItLogsSlowResponseEmergencyToSlackChannel()
    {
        $this->mock(TimeHelper::class, function (MockInterface $mock) {
            $mock->shouldReceive('getResponseTime')
                ->andReturn(11);
        });

        $mockLogger = $this->mock(Logger::class, function (MockInterface $mock) {
            $mock->shouldReceive('log')
                ->once()
                ->with('warning', 'Response time 11.00 s for /include/test is greater than limit of 3.00 s');
            $mock->shouldReceive('log')
                ->once()
                ->with('emergency', 'Response time 11.00 s for /include/test is greater than limit of 10.00 s');
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
}
