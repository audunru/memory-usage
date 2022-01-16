<?php

namespace audunru\MemoryUsage\Contracts;

interface MemoryHelperContract
{
    public function getPeakUsage(): float;
}
