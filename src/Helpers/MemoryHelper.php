<?php

namespace audunru\MemoryUsage\Helpers;

use audunru\MemoryUsage\Contracts\MemoryHelperContract;

class MemoryHelper implements MemoryHelperContract
{
    public function getPeakUsage(): float
    {
        return memory_get_peak_usage() / 1024 / 1024;
    }
}
