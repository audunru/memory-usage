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
                    'ignore_patterns'     => ['include/higher*', 'include/lower*'],
                    'slow_response_limit' => 3,
                    'channel'             => null,
                    'level'               => 'warning',
                ],
                [
                    'patterns'            => ['include/higher'],
                    'ignore_patterns'     => [],
                    'slow_response_limit' => 15,
                    'channel'             => null,
                    'level'               => 'warning',
                ],
                [
                    'patterns'            => ['include/lower'],
                    'ignore_patterns'     => [],
                    'slow_response_limit' => 1,
                    'channel'             => null,
                    'level'               => 'warning',
                ],
                [
                    'patterns'            => ['include*'],
                    'ignore_patterns'     => [],
                    'slow_response_limit' => 30,
                    'channel'             => 'slack',
                    'level'               => 'emergency',
                ],
            ],
            'memory-usage.ignore_patterns' => [
                'ignore*',
            ],
        ]);

        $this->mock(MemoryHelper::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPeakUsage')
                ->andReturn(1000);
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

    public function testItLogsSlowResponseWarningToDefaultChannelForHigherLimitRoute()
    {
        $this->mock(TimeHelper::class, function (MockInterface $mock) {
            $mock->shouldReceive('getResponseTime')
                ->andReturn(16);
        });

        $mockLogger = $this->mock(Logger::class, function (MockInterface $mock) {
            $mock->shouldReceive('log')
                ->once()
                ->with('warning', 'Response time 16.00 s for /include/higher is greater than limit of 15.00 s');
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

        $this->get('/include/higher');
    }

    public function testItLogsSlowResponseWarningToDefaultChannelForLowerLimitRoute()
    {
        $this->mock(TimeHelper::class, function (MockInterface $mock) {
            $mock->shouldReceive('getResponseTime')
                ->andReturn(2);
        });

        $mockLogger = $this->mock(Logger::class, function (MockInterface $mock) {
            $mock->shouldReceive('log')
                ->once()
                ->with('warning', 'Response time 2.00 s for /include/lower is greater than limit of 1.00 s');
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

        $this->get('/include/lower');
    }

    public function testItLogsSlowResponseEmergencyToSlackChannel()
    {
        $this->mock(TimeHelper::class, function (MockInterface $mock) {
            $mock->shouldReceive('getResponseTime')
                ->andReturn(31);
        });

        $mockLogger = $this->mock(Logger::class, function (MockInterface $mock) {
            $mock->shouldReceive('log')
                ->once()
                ->with('warning', 'Response time 31.00 s for /include/test is greater than limit of 3.00 s');
            $mock->shouldReceive('log')
                ->once()
                ->with('emergency', 'Response time 31.00 s for /include/test is greater than limit of 30.00 s');
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
