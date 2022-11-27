<?php

namespace audunru\MemoryUsage\Tests\Feature;

use audunru\MemoryUsage\Helpers\TimeHelper;
use audunru\MemoryUsage\Tests\TestCase;

class TimeHelperTest extends TestCase
{
    public function testItReturnsMemoryUsage()
    {
        $timeHelper = new TimeHelper();

        $result = $timeHelper->getResponseTime();

        $this->assertIsFloat($result);
        $this->assertGreaterThan(0, $result);
    }
}
