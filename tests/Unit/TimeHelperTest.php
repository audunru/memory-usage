<?php

namespace audunru\MemoryUsage\Tests\Unit;

use audunru\MemoryUsage\Helpers\TimeHelper;
use audunru\MemoryUsage\Tests\TestCase;

class TimeHelperTest extends TestCase
{
    public function test_it_returns_memory_usage()
    {
        $timeHelper = new TimeHelper;

        $result = $timeHelper->getResponseTime();

        $this->assertIsFloat($result);
        $this->assertGreaterThan(0, $result);
    }
}
