<?php

namespace audunru\MemoryUsage\Tests\Unit;

use audunru\MemoryUsage\Helpers\MemoryHelper;
use audunru\MemoryUsage\Tests\TestCase;

class MemoryHelperTest extends TestCase
{
    public function test_it_returns_memory_usage()
    {
        $memoryHelper = new MemoryHelper;

        $result = $memoryHelper->getPeakUsage();

        $this->assertIsFloat($result);
        $this->assertGreaterThan(0, $result);
    }
}
