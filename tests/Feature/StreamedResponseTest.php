<?php

namespace audunru\MemoryUsage\Tests\Feature;

use audunru\MemoryUsage\Helpers\MemoryHelper;
use audunru\MemoryUsage\Helpers\TimeHelper;
use audunru\MemoryUsage\Tests\TestCase;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;

class StreamedResponseTest extends TestCase
{
    private static bool $streamed = false;

    protected function setUp(): void
    {
        parent::setUp();

        self::$streamed = false;

        config([
            'memory-usage.paths' => [
                [
                    'patterns' => ['stream*'],
                    'ignore_patterns' => [],
                    'limit' => 10,
                    'slow_response_limit' => 10,
                    'channel' => null,
                    'level' => 'warning',
                    'header' => [
                        'environments' => ['testing'],
                    ],
                ],
            ],
        ]);
    }

    protected function defineRoutes($router)
    {
        $router->get('/stream', function () {
            return response()->stream(function () {
                self::$streamed = true;

                echo 'streamed';
            });
        });
    }

    public function test_it_logs_memory_usage_after_response_is_streamed()
    {
        $this->mock(MemoryHelper::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPeakUsage')
                ->andReturnUsing(fn () => self::$streamed ? 11 : 0);
        });

        $this->mock(TimeHelper::class, function (MockInterface $mock) {
            $mock->shouldReceive('getResponseTime')
                ->andReturn(0);
        });

        $mockLogger = $this->mock(Logger::class, function (MockInterface $mock) {
            $mock->shouldReceive('log')
                ->once()
                ->with('warning', 'Maximum memory 11.00 MiB used during request for /stream is greater than limit of 10.00 MiB');
        });

        Log::shouldReceive('channel')
            ->once()
            ->with(null)
            ->andReturn($mockLogger);

        $this->sendAndTerminate('/stream');
    }

    public function test_it_logs_slow_response_after_response_is_streamed()
    {
        $this->mock(MemoryHelper::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPeakUsage')
                ->andReturn(0);
        });

        $this->mock(TimeHelper::class, function (MockInterface $mock) {
            $mock->shouldReceive('getResponseTime')
                ->andReturnUsing(fn () => self::$streamed ? 11 : 0);
        });

        $mockLogger = $this->mock(Logger::class, function (MockInterface $mock) {
            $mock->shouldReceive('log')
                ->once()
                ->with('warning', 'Response time 11.00 s for /stream is greater than limit of 10.00 s');
        });

        Log::shouldReceive('channel')
            ->once()
            ->with(null)
            ->andReturn($mockLogger);

        $this->sendAndTerminate('/stream');
    }

    public function test_it_does_not_add_header_to_streamed_response()
    {
        $this->mock(MemoryHelper::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPeakUsage')
                ->andReturn(1);
        });

        $response = $this->get('/stream');

        $response->assertHeaderMissing('memory-usage');
    }

    /**
     * Handle, send and terminate a request in the same order as Application::handleRequest().
     */
    private function sendAndTerminate(string $uri): void
    {
        $kernel = $this->app->make(Kernel::class);
        $request = Request::create($uri);

        $response = $kernel->handle($request);

        ob_start();
        $response->send();
        ob_end_clean();

        $kernel->terminate($request, $response);
    }
}
