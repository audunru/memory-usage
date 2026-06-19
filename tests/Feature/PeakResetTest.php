<?php

namespace audunru\MemoryUsage\Tests\Feature;

use audunru\MemoryUsage\Helpers\MemoryHelper;
use audunru\MemoryUsage\Helpers\TimeHelper;
use audunru\MemoryUsage\Tests\TestCase;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;

class PeakResetTest extends TestCase
{
    public function test_it_resets_peak_memory_usage_when_route_is_matched()
    {
        // Allocate a large chunk to drive the peak up
        $data = str_repeat('x', 10 * 1024 * 1024);
        $peakBeforeRequest = memory_get_peak_usage();

        unset($data);

        $this->mock(TimeHelper::class, function (MockInterface $mock) {
            $mock->shouldReceive('getResponseTime')->andReturn(0);
        });

        $this->mock(MemoryHelper::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPeakUsage')->andReturn(0);
        });

        Log::spy();

        $this->get('/ignore');

        $this->assertLessThan($peakBeforeRequest, memory_get_peak_usage());
    }
}
