<?php

namespace audunru\MemoryUsage\Helpers;

class TimeHelper
{
    public function getResponseTime(): float
    {
        return microtime(true) - LARAVEL_START;
    }
}
