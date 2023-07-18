<?php

namespace audunru\MemoryUsage\Tests\Unit;

use audunru\MemoryUsage\Helpers\MemoryHelper;
use audunru\MemoryUsage\Tests\TestCase;

class MemoryHelperTest extends TestCase
{
    public function testItReturnsMemoryUsage()
    {
        $memoryHelper = new MemoryHelper();

        $result = $memoryHelper->getPeakUsage();

        $this->assertIsFloat($result);
        $this->assertGreaterThan(0, $result);
    }
}
