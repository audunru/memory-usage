<?php

namespace audunru\MemoryUsage\Helpers;

class MemoryHelper
{
    public function getPeakUsage(): float
    {
        return memory_get_peak_usage() / 1024 / 1024;
    }
}
